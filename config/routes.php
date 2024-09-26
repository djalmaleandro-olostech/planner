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
use App\Middleware\ApiKeyManagerMiddleware;
use App\Middleware\ApiKeyMiddleware;
use Hyperf\HttpServer\Router\Router;

Router::addRoute(['GET', 'POST', 'HEAD'], '/live', 'App\Controller\IndexController@index');


Router::addGroup(
    '/api',
    function () {
        Router::addRoute(['POST'], '/login', 'App\Controller\UsuarioAppController@login');
    }
);

Router::addGroup(
    '/api/apikey',
    function () {
        Router::addRoute(['POST'], '/create', 'App\Controller\ApiKeyController@createApiKey');
        Router::addRoute(['POST'], '/activate', 'App\Controller\ApiKeyController@activateApiKey');
        Router::addRoute(['POST'], '/deactivate', 'App\Controller\ApiKeyController@deactivateApiKey');
    },
    ['middleware' => [ApiKeyManagerMiddleware::class]]
);

Router::addGroup(
    '/api',
    function () {
        Router::addRoute(['GET', 'POST', 'HEAD'], '/live-user', 'App\Controller\IndexController@user');
    },
   ['middleware' => [ApiKeyMiddleware::class]]
);

Router::get('/favicon.ico', function () {
    return '';
});
