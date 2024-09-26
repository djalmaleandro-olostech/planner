<?php

declare(strict_types=1);

namespace App\Repository;

use App\Helper\Database;

final class UsuarioAppRepository
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function returnUsuarioByCredentials(string $cpf, string $hash)
    {
        $sql = "SELECT
                    usuario_app.id AS id_usuario_app,
                    usuario_app.id_pessoa,
                    usuario_sus.id AS id_usuario_sus,
                    usuario_sus.matricula,
                    pessoa.nome
                FROM public.usuario_app
                INNER JOIN pessoa 
                    ON pessoa.id = usuario_app.id_pessoa
                INNER JOIN usuario_sus 
                    ON usuario_sus.id = usuario_app.id_pessoa
                WHERE login = '$cpf' AND senha = '$hash'";
        $result = $this->db->run($sql);
        return !empty($result) ? $result[0] : null;
    }
    public function getUsuarioApp(int $idUsuarioApp)
    {   
        $sql = "SELECT
                    usuario_app.id AS id_usuario_app, 
                    usuario_app.id_pessoa, 
                    usuario_sus.id AS id_usuario_sus, 
                    pessoa.nome, 
                    usuario_sus.matricula
                FROM public.usuario_app
                INNER JOIN pessoa 
                    ON pessoa.id = usuario_app.id_pessoa
                INNER JOIN usuario_sus 
                    ON usuario_sus.id = usuario_app.id_pessoa
                WHERE usuario_app.id = $idUsuarioApp";
        $result = $this->db->run($sql);
        return !empty($result) ? $result[0] : null;
    }
}
