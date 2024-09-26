<?php

declare(strict_types=1);

namespace App\Middleware;

use Carbon\Carbon;
use Hyperf\Config\ConfigFactory;
use Hyperf\Context\ApplicationContext;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiKeyManagerMiddleware implements MiddlewareInterface
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
	protected $logger;

	protected $token;
  

	public function __construct(
		ContainerInterface $container,
		HttpResponse $response,
		RequestInterface $request,
		StdoutLoggerInterface $logger,
	)
	{
		$this->container = $container;
		$this->response = $response;
		$this->request = $request;
		$config = new ConfigFactory;
    	$config = $config(ApplicationContext::getContainer());
   		$this->token = $config->get("key_manager.default.token");
		$this->logger = $logger;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$apiKey = $this->request->getParsedBody();
		if (!$apiKey["token"] ) {
			return $this->response->json([
				'code' => 'NO_TOKEN',
				'message' => 'Token não fornecido'
			]);
		}

		if ($apiKey["token"] != $this->token) {
			return $this->response->json([
				'code' => 'WRONG_TOKEN',
				'message' => 'O token fornecido é inválido' 
			]);
		}

		return $handler->handle($request);
	}
}