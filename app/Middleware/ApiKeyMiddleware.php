<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Service\ApiKeyService;
use Carbon\Carbon;
use Hyperf\Context\Context;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiKeyMiddleware implements MiddlewareInterface
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
	protected ApiKeyService $apiKeyService;
	public function __construct(
		ContainerInterface $container,
		HttpResponse $response,
		RequestInterface $request,
		StdoutLoggerInterface $logger,
		ApiKeyService $apiKeyService
	)
	{
		$this->container = $container;
		$this->response = $response;
		$this->request = $request;
		$this->logger = $logger;
		$this->apiKeyService = $apiKeyService;
	}

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$apiKey = $request->getHeaderLine('api-key-prod') ?: $request->getHeaderLine('api-key-dev');
		if (empty($apiKey)) {
			return $this->response->json([
				'code' => 'MISSING_API_KEY',
				'message' => 'No API key provided'
			]);
		}

		$credentials = $this->getCredentialsByApiKey($apiKey);
		if (empty($credentials['usuario'])) {
			return $this->response->json($credentials);
		}
		
		$request = $request->withAttribute('loggedInApiKey', json_encode($credentials['usuario']));
		Context::set(ServerRequestInterface::class, $request);

		return $handler->handle($request);
	}
	private function getCredentialsByApiKey($apiKey)
	{	
		$resultApiKey = $this->apiKeyService->getApiKey($apiKey);
		if(!isset($resultApiKey['api_key'])){
			return $resultApiKey;
		}
		$response['usuario'] = $resultApiKey;
		return $response;
	}
}