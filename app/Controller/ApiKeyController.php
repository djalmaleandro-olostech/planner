<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\ApiKeyService;
use Hyperf\HttpServer\Contract\RequestInterface;

class ApiKeyController extends AbstractController
{
    private ApiKeyService $apiKeyService;

    public function __construct(
        ApiKeyService $apiKeysService,
    ){  
        $this->apiKeyService = $apiKeysService;
    }

    public function createApiKey(RequestInterface $request)
    {   
        $requestParams = [
            'identificacao' => $request->input('identificacao'),
            'validade'      => $request->input('validade'),
            'tipo_conta'    => $request->input('tipo_conta')
        ];
        if (array_filter($requestParams) != $requestParams) {
            return [
                'code'    => 'MISSING_DATA',
                'message' => 'Faltam dados obrigatórios!'
            ];
        }
        return $this->apiKeyService->createApiKey($requestParams);
    }

    public function activateApiKey(RequestInterface $request)
    {
        $requestParams = [
            'identificacao' => $request->input('identificacao'),
            'key'           => $request->input('key')
        ];
        if (array_filter($requestParams) != $requestParams) {
            return [
                'code'    => 'MISSING_DATA',
                'message' => 'Faltam dados obrigatórios!'
            ];
        }
        return $this->apiKeyService->activateApiKey($requestParams);
    }

    public function deactivateApiKey(RequestInterface $request)
    {
        $requestParams = [
            'identificacao' => $request->input('identificacao'),
            'key'           => $request->input('key')
        ];
        if (array_filter($requestParams) != $requestParams) {
            return [
                'code'    => 'MISSING_DATA',
                'message' => 'Faltam dados obrigatórios!'
            ];
        }
        return $this->apiKeyService->deactivateApiKey($requestParams);
    }
}