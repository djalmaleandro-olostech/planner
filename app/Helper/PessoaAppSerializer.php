<?php

namespace App\Helper;

use App\Domain\Entity\PessoaApp;

class PessoaAppSerializer
{   
    public static function serialize(array $pessoa): string
    {
        return json_encode([
            'id_usuario_app' => $pessoa['id_usuario_app'],
            'id_pessoa'      => $pessoa['id_pessoa'],
            'matricula'      => $pessoa['matricula'],
            'id_usuario_sus' => $pessoa['id_usuario_sus'],
            'nome'           => $pessoa['nome']
        ]);
    }
    
    public static function deserialize(array $aPessoa): PessoaApp
    {
        $oPessoa = new PessoaApp();
        $oPessoa
                ->setIdUsuarioApp($aPessoa['id_usuario_app'])
                ->setNome($aPessoa['nome'])
                ->setIdPessoa($aPessoa['id_pessoa'])
                ->setIdUsuarioSus($aPessoa['id_usuario_sus']);
        
        if(isset($aPessoa['matricula'])){
            $oPessoa->setMatricula($aPessoa['matricula']);
        }
        return $oPessoa;
    }
}