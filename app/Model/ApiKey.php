<?php

declare(strict_types=1);

namespace App\Model;

use Carbon\Carbon;
use App\Enum\TipoContaEnum;

class ApiKey
{
    protected string $identificacao;
    protected string $validade;
    protected string $apiKey;
    protected string $tipoConta;
    protected bool $ativo;
    protected string $createdAt;

    public function setIdentificacao(string $identificacao): self
    {
        $this->identificacao = $identificacao;
        return $this;
    }
    public function getIdentificacao(): string
    {
        return $this->identificacao;
    }
    public function setValidade(string $validade): self
    {
        $this->validade = $validade;
        return $this;
    }
    public function getValidade(): string
    {
        return $this->validade;
    }
    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;
        return $this;
    }
    public function getApiKey(): string
    {
        return $this->apiKey;
    }
    public function setTipoConta(string $tipoConta): self
    {   
        try {
            TipoContaEnum::from($tipoConta);
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Tipo de conta inválido.");
        }
        $this->tipoConta = $tipoConta;
        return $this;
    }
    public function getTipoConta(): string
    {
        return $this->tipoConta;
    }
    public function setAtivo(bool $ativo): self
    {
        $this->ativo = $ativo;
        return $this;
    }
    public function getAtivo(): bool
    {
        return $this->ativo;
    }
    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }
    public function setCreatedAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }
    public function isExpired(): bool
    {
        $now = Carbon::now()->setTime(00, 00);
        return $now->greaterThan($this->validade);
    }
    public static function serialize(array $apiKey): string
    {
        return json_encode([
            'id'            => $apiKey['id'],
            'identificacao' => $apiKey['identificacao'],
            'validade'      => $apiKey['validade'],
            'apiKey'        => $apiKey['api_key'],
            'tipoConta'     => $apiKey['tipo_conta'],
            'ativo'         => $apiKey['ativo'],
            'createdAt'     => $apiKey['created_at'],
        ]);
    }
    public static function deserialize(array $aApiKey): ApiKey
    {
        $apiKey = new ApiKey();
        $apiKey->setIdentificacao($aApiKey['identificacao'])
                ->setValidade($aApiKey['validade'])
                ->setApiKey($aApiKey['api_key'])
                ->setTipoConta($aApiKey['tipo_conta'])
                ->setAtivo($aApiKey['ativo'])
                ->setCreatedAt($aApiKey['created_at']);
        return $apiKey;
    }
}
