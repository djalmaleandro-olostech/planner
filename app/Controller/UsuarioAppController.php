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
namespace App\Controller;

use App\Helper\Jwt;
use App\Service\UsuarioAppService;
use Hyperf\HttpServer\Contract\RequestInterface;

class UsuarioAppController extends AbstractController
{
    private UsuarioAppService $usuarioAppService;
    private Jwt $jwt;

    public function __construct(
        UsuarioAppService $usuarioAppService,
        Jwt $jwt
    ){  
        $this->usuarioAppService = $usuarioAppService;
        $this->jwt = $jwt;
    }

    public function login(RequestInterface $request)
    {   
        if($request->input('login') == '' || $request->input('senha') == ''){
            return [
                'code' => 'MISSING_CREDENTIALS',
                'message' => 'Missing credentials'
            ];
        }

        $usuario = $this->usuarioAppService->returnUsuarioByCredentials(
            $request->input('login'), $request->input('senha')
        );

        if(empty($usuario)){
            return [
                'code' => 'INVALID_CREDENTIALS',
                'message' => 'invalid credentials'
            ];
        }
        
        return [
            'usuario' => $usuario['nome'],
            'idUsuarioApp' => $usuario['idUsuarioApp'],
            'token' => $this->jwt->encode($usuario)
        ];
    }
    public function returnUsuarioByCredentials(RequestInterface $request): mixed
    {
        return $request->getAttribute('loggedInUser');
    }
}
