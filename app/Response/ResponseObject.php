<?php

namespace App\Response;

use Psr\Http\Message\RequestInterface as PsrRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

class ResponseObject
{
    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function status()
    {
        return 200;
    }

    public function toResponse(PsrRequestInterface $request): PsrResponseInterface
    {
        $response = \Hyperf\Utils\Context::get(PsrResponseInterface::class);
        return $response->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream($this->data));
    }
}