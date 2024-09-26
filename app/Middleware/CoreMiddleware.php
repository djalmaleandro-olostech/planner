<?php
namespace App\Middleware;

use Hyperf\Codec\Json;
use Hyperf\HttpMessage\Server\ResponsePlusProxy;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Contract\Arrayable;
use Hyperf\Contract\Jsonable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use App\Response\ResponseObject;
use Swow\Psr7\Message\ResponsePlusInterface;


class CoreMiddleware extends \Hyperf\HttpServer\CoreMiddleware implements MiddlewareInterface
{

    /**
     * Transfer the non-standard response content to a standard response object.
     * @param array|Arrayable|Jsonable|string $response
     */
    protected function transferToResponse($response, ServerRequestInterface $request): ResponsePlusInterface
    {
        if (is_string($response)) {
            return $this->response()->addHeader('content-type', 'text/plain')->setBody(new SwooleStream($response));
        }

        if ($response instanceof ResponseInterface) {
            return new ResponsePlusProxy($response);
        }

        if (is_array($response) || $response instanceof Arrayable) {
            return $this->response()
                ->addHeader('content-type', 'application/json')
                ->setBody(new SwooleStream(Json::encode(
                    $response,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES |
                    JSON_NUMERIC_CHECK | JSON_PRESERVE_ZERO_FRACTION
                )));
        }
        if ($response instanceof Jsonable) {
            return $this->response()
                ->addHeader('content-type', 'application/json')
                ->setBody(new SwooleStream((string) $response));
        }

        if ($this->response()->hasHeader('content-type')) {
            return $this->response()->setBody(new SwooleStream((string) $response));
        }

        if ($response instanceof ResponseObject) {
            return $response->toResponse($request);
        }

        return $this->response()->addHeader('content-type', 'text/plain')->setBody(new SwooleStream((string) $response));
    }
}