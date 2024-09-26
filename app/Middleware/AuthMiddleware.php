<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Helper\Jwt;
use App\Service\UsuarioAppService;
use Carbon\Carbon;
use Hyperf\Config\ConfigFactory;
use Hyperf\Context\Context;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\Contract\StdoutLoggerInterface;

class AuthMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var HttpResponse
     */
    protected HttpResponse $response;
    public const alg = 'HS256';
    private string $jwtSecret;
    protected $logger;
    protected $jwt;

    private UsuarioAppService $usuarioAppService;

    public function __construct(
        ContainerInterface $container,
        HttpResponse $response,
        RequestInterface $request,
        StdoutLoggerInterface $logger,
        UsuarioAppService $usuarioAppService,
        Jwt $jwt
    ) {
        $this->container = $container;
        $this->response = $response;
        $this->request = $request;
        $this->usuarioAppService = $usuarioAppService;
        $config = new ConfigFactory;
        $config = $config(ApplicationContext::getContainer());
        $this->jwtSecret = $config->get("jwt.default.secret");
        $this->logger = $logger;
        $this->jwt = $jwt;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeader('Authorization')[0] ?? '';
        $apiToken = preg_replace(
            '/Bearer /i',
            '',
            $token
        );

        if ($apiToken == '') {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            $data = [
                'code' => 'AUTH_MISSING_CREDENTIALS',
                'message' => 'missing token'
            ];
            return $this->response->json($data);
        }

        // the request object is immutable, so we clone it , add the desired attributes and
        // then return it to be handled by the controller
        // To retrieve the desired data, just do a request->getAttribute('attribute') at the controller
        // Checking JWT
        $credentials = $this->getCredentials($apiToken);
        if (!$credentials['pessoa']) {
            return $this->response->json($credentials['error']);
        }

        $request = Context::get(ServerRequestInterface::class);
        $request = $request->withAttribute('loggedInUser', json_encode($credentials['pessoa']));
        Context::set(ServerRequestInterface::class, $request);
        return $handler->handle($request);
    }

    public function getJwtSecret(): string
    {
        return $this->jwtSecret;
    }

    private function isJwt($token): bool
    {
        return strlen($token) > 201;
    }

    private function isTokenExpired($validade): bool
    {   
        $now = Carbon::now();
        $expireTime = Carbon::parse($validade);
        return $now->greaterThan($expireTime);
    }

    private function getCredentials($token)
    {  
        $response = []; 
        if(!$this->isJwt($token)){
            $response['pessoa'] = null;
            $response['error'] = [
                'code' => 'INCOMPLETE_JWT',
                'message' => 'incomplete jwt string'
            ];
            return $response;
        }
        try {
            $decodedJwt = $this->jwt->decode($token);
            if($this->isTokenExpired($decodedJwt->validade)){
                $response['pessoa'] = null;
                $response['error'] = [
                    'code' => 'EXPIRED_JWT',
                    'message' => 'Jwt expired'
                ];
                return $response;
            }
        } catch (\Throwable $th) {
            $response['pessoa'] = null;
            $response['error'] = [
                'code' => 'INVALID_JWT',
                'message' => 'Invalid jwt string'
            ];
            return $response;
        }
        $response['pessoa'] = $this->usuarioAppService->getUsuarioApp($decodedJwt->idUsuarioApp);
        return $response;
    }
}