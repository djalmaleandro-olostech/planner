<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\ApiKey;
use App\Repository\ApiKeyRepository;
use App\Trait\CacheInvalidator;
use App\Helper\RedisDriver;
use App\Helper\TransactionManager;
use Hyperf\Config\ConfigFactory;
use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\StdoutLoggerInterface;
use Carbon\Carbon;

class ApiKeyService
{   
    use CacheInvalidator;
    private ApiKeyRepository $apiKeyRepository;
    private RedisDriver $redis;
    private TransactionManager $transactionManager;
    private StdoutLoggerInterface $logger;
    private string $headerApiKey;
	private string $hashAlgorithm;
	private const KEY_PARTS = 3;
	private const HEADER_KEY_POSITION = 0;
	private const PAYLOAD_KEY_POSITION = 1;
	private const SECRET_KEY_POSITION = 2;

    public function __construct(
        ApiKeyRepository $apiKeyRepository,
        RedisDriver $redis,
        TransactionManager $transactionManager,
        StdoutLoggerInterface $logger
    ) {
        $this->apiKeyRepository = $apiKeyRepository;
        $this->redis = $redis;
        $this->transactionManager = $transactionManager;
        $this->logger = $logger;
        $config = new ConfigFactory;
		$config = $config(ApplicationContext::getContainer());
		$this->headerApiKey = $config->get("header_api_key.default.header");
		$this->hashAlgorithm = $config->get("hash_algorithm.default.hash_algorithm");
    }

    public function createApiKey(array $data): array
    {   
        $this->transactionManager->beginTransaction();
        try {
            $hashApiKey = $this->generateApiKey($data);
            if($this->apiKeyRepository->getKeyByKey($hashApiKey) != null){
                $this->logger->error("KEY_DUPLICADA: $hashApiKey");
                return [
                    'code'    => 'KEY_DUPLICADA',
                    'message' => 'Esta key já está cadastrada'
                ];
            }
            
            $apiKey = new ApiKey();
            $apiKey->setIdentificacao($data['identificacao'])
                    ->setValidade($this->calcularValidade($data['validade']))
                    ->setTipoConta($data['tipo_conta'])
                    ->setApiKey($hashApiKey);
            
            $this->invalidateCache($hashApiKey);
            $this->apiKeyRepository->deactivateOlderKeys($apiKey->getIdentificacao());
            $result = $this->apiKeyRepository->createApiKey($apiKey);
            $this->transactionManager->commit();

            if($result == false){
                return [
                    'code'    => 'DB_FAILURE',
                    'message' => 'Registro não inserido'
                ];
            }
            return $this->apiKeyRepository->getKeyByKey($hashApiKey);

        } catch (\InvalidArgumentException $e) {
            $this->transactionManager->rollBack();
            return [
                'code'    => 'INVALID_DATA',
                'message' => $e->getMessage()
            ];
        }
    }
    
    public function getApiKey(string $hashApiKey): mixed
    {
        if(!$this->isApiKeyValid($hashApiKey)){
            return [
				'code' => 'INVALID_API_KEY',
				'message' => 'Invalid api key'
			];
        }
        $result = $this->apiKeyRepository->getKeyByKey($hashApiKey);
        if(ApiKey::deserialize($result)->isExpired()){
            return [
                'code'    => 'KEY_EXPIRED',
                'message' => 'Key expirada.'
            ];
        }
        return $result;
    }

    public function activateApiKey(array $data): array
    {
        try {
            $this->transactionManager->beginTransaction();
            $resultKey = $this->apiKeyRepository->getKeyByKey($data['key']);
            if (!$resultKey) {
                 return [
                    'code'    => 'KEY_NOTFOUND',
                    'message' => 'Key não existe.'
                ];
            }
            $apiKey = ApiKey::deserialize($resultKey);
            if ($apiKey->isExpired()) {
                return [
                    'code'    => 'KEY_EXPIRED',
                    'message' => 'Key expirada.'
                ];
            }
            if ($apiKey->getAtivo()) {
                return [
                    'code'    => 'ACTIVE_KEY',
                    'message' => 'Key já está ativada.'
                ];
            }
            $this->apiKeyRepository->deactivateOlderKeys($data['identificacao']);
            $result = $this->apiKeyRepository->activateKey($data['identificacao'], $data['key']);

            if($result == false){
                $this->transactionManager->rollBack();
                $this->logger->error('Falha ao atualizar chave %s', [ 'valor' => $data['key']]);
                return [
                    'code'    => 'DB_FAILURE',
                    'message' => 'Registro não atualizado'
                ];
            }
            $this->transactionManager->commit();
            $this->invalidateCache($data['key']);
            return [
                    'code'    => 'KEY_ACTIVED',
                    'message' => 'Key ativada'
                ];
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->transactionManager->rollBack();
            return [
                'code'    => 'INVALID_DATA',
                'message' => 'Falha ao ativar a chave'
            ];
        }

    }

    public function deactivateApiKey(array $data): array
    {
        $resultKey = $this->apiKeyRepository->getKeyByKey($data['key']);
        if (!$resultKey) {
                return [
                'code' => 'KEY_NOTFOUND',
                'message' => 'Key não existe.'
            ];
        }
        $apiKey = ApiKey::deserialize($resultKey);
        if (!$apiKey->getAtivo()) {
            return [
                'code' => 'INACTIVE_KEY',
                'message' => 'Key já está desativada.'
            ];
        }
        $result = $this->apiKeyRepository->deactivateKey($data['identificacao'], $data['key']);

        if($result == false){
            return [
                'code' => 'DB_FAILURE',
                'message' => 'Registro não atualizado'
            ];
        }
        $this->invalidateCache($data['key']);
        return [
            'code' => 'KEY_INACTIVED',
            'message' => 'Key desativada'
        ];
    }
    
    private function calcularValidade(int $diasValidade): string
    {
        return Carbon::now()->addDays($diasValidade)->toDateString();
    }

    public function generateApiKey(array $data): string
	{   
        $apiKeyParams = $data["identificacao"] . Carbon::now()->toDateTimeString() . $data["tipo_conta"];
		$header = $this->generateHeader();
		$payload = strtoupper(hash($this->hashAlgorithm, $apiKeyParams));
		$secret = $this->generateSecret($payload);
		return "$header.$payload.$secret";
	}

	private function generateHeader(): string
	{
		$mountHeader = hash($this->hashAlgorithm, $this->headerApiKey);
		return strtoupper(substr($mountHeader, 10, 5));
	}

	private function generateSecret(string $payload): string
	{   
		$mountSecret = hash($this->hashAlgorithm, $payload);
		return strtoupper(substr($mountSecret, 10, 5));
	}

	public function isApiKeyValid(string $apiKey): bool
	{   
		$apiKeyParts = explode('.', $apiKey);
		if (count($apiKeyParts) !== self::KEY_PARTS) {
			$this->logger->error('API_KEY_MIDDLEWARE: Api key inválida.');
            return false;
		}
		if ($apiKeyParts[self::HEADER_KEY_POSITION] !== $this->generateHeader()) {
			$this->logger->error('API_KEY_MIDDLEWARE: Api key inválida.');
            return false;
		}
		if ($apiKeyParts[self::SECRET_KEY_POSITION] !== $this->generateSecret($apiKeyParts[self::PAYLOAD_KEY_POSITION])) {
			$this->logger->error('API_KEY_MIDDLEWARE: Api key inválida.');
            return false;
		}
        return true;
	}
}
