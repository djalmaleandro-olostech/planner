<?php

declare(strict_types=1);

namespace App\Repository;

use App\Helper\DatabaseGateway;
use App\Model\ApiKey;
use Hyperf\Cache\Annotation\Cacheable;
use Hyperf\Contract\StdoutLoggerInterface;

final class ApiKeyRepository
{
    private DatabaseGateway $db;
    private StdoutLoggerInterface $logger;

    public function __construct(DatabaseGateway $db, StdoutLoggerInterface $logger)
    {   
        $this->db = $db;
        $this->logger = $logger;
    }
    #[Cacheable(prefix: "apikey-getkeybykey", ttl: 900, listener: "apikey-getkeybykey")]
    public function getKeyByKey(string $key): mixed
    {   
        $sql = "SELECT
                    api_auth.id,
                    api_auth.identificacao, 
                    api_auth.validade,
                    api_auth.created_at, 
                    api_auth.ativo,
                    api_auth.api_key,
                    api_auth.tipo_conta
                FROM
                    api_auth
                WHERE 
                    api_auth.api_key = '$key'";
        return $this->db->fetch($sql);
    }

    public function activateKey(string $identificacao, string $key): mixed
    {   
        try {     
            $sql = "UPDATE
                        api_auth
                    SET
                        ativo = true
                    WHERE 
                        api_key = '$key'
                    AND 
                        identificacao = '$identificacao'
                    AND 
                        ativo IS false";
            return $this->db->execute($sql);
        } catch (\Throwable $th) {
            $this->logger->warning($th->getMessage());
        }
        return false;
    }

    public function deactivateKey(string $identificacao, string $key): mixed
    {   
        try {            
            $sql = "UPDATE
                        api_auth
                    SET
                        ativo = false
                    WHERE 
                        api_key = '$key'
                    AND 
                        identificacao = '$identificacao'";
            return $this->db->execute($sql);
        } catch (\Throwable $th) {
            $this->logger->warning($th->getMessage());
        }
        return false;
    }
    public function deactivateOlderKeys(string $identificacao): mixed
    {   
        try {
            $sql = "UPDATE
                        api_auth
                    SET
                        ativo = false
                    WHERE 
                        identificacao = '$identificacao'
                    AND
                        ativo IS true";
            return $this->db->execute($sql);
        } catch (\Throwable $th) {
            $this->logger->warning($th->getMessage());
        }
        return false;
    }

    public function createApiKey(ApiKey $apiKey): bool
    {   
        try {
            $table = 'api_auth';
            $values = [
                'identificacao' => $apiKey->getIdentificacao(),
                'validade'      => $apiKey->getValidade(),
                'api_key'       => $apiKey->getApiKey(),
                'tipo_conta'    => $apiKey->getTipoConta(),
                'created_at'    => 'NOW()'
            ];
            return $this->db->insert($table, $values);
        } catch (\Throwable $th) {
            $this->logger->warning($th->getMessage());
        }
        return false;
    }
}