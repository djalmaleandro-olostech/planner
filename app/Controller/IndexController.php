<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\TrendingService;
use Psr\Http\Message\ResponseInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

class IndexController extends AbstractController
{

    public function index(RequestInterface $request, HttpResponse $response): ResponseInterface
    {
        return $response->json(['alive' => true]);

    }

    public function user(RequestInterface $request)
    {
        return ["loggedInApiKey" => json_decode($request->getAttribute('loggedInApiKey'), true)];
    }
}
