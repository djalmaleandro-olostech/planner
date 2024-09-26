<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Service;

use App\Helper\PessoaAppSerializer;
use App\Helper\RedisDriver;
use App\Repository\UsuarioAppRepository;
class UsuarioAppService
{
    private UsuarioAppRepository $usuarioAppRepository;
    private RedisDriver $redis;
    private string $prefix = 'pessoaCredential';

    public function __construct(
        UsuarioAppRepository $usuarioAppRepository,
        RedisDriver $redis,
    ) {
        $this->usuarioAppRepository = $usuarioAppRepository;
        $this->redis = $redis;
    }

    public function returnUsuarioByCredentials(string $cpf, string $hash): array
    {   
        $cachedPessoa = $this->redis->hget($this->prefix, (string) $cpf);
        if(!$cachedPessoa){
            $usuario = $this->usuarioAppRepository->returnUsuarioByCredentials($cpf, $hash);
            if($usuario == null){
                return [];
            }
            $this->redis->hset($this->prefix, $cpf, PessoaAppSerializer::serialize($usuario));
            return (array) PessoaAppSerializer::deserialize($usuario);
        }
        return (array) PessoaAppSerializer::deserialize(
            json_decode($cachedPessoa, true)
        );
    }

    public function getUsuarioApp(int $idUsuarioApp)
    {
        $result = $this->usuarioAppRepository->getUsuarioApp($idUsuarioApp);
        if($result == null){
            return null;
        }
        return PessoaAppSerializer::deserialize($result);
    }
}
