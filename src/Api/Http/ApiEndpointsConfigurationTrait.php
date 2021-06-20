<?php

declare(strict_types=1);

namespace App\Api\Http;

use Symfony\Component\HttpFoundation\Request;

trait ApiEndpointsConfigurationTrait
{
    private function isApiRequest(Request $request): bool
    {
        return $request->attributes->getBoolean('_api_endpoint');
    }

    private function isApiReceiveEnabled(Request $request): bool
    {
        return $this->isApiRequest($request) && $request->attributes->getBoolean('_api_receive');
    }

    private function isApiUpdateEnabled(Request $request): bool
    {
        return $this->isApiRequest($request) && $request->attributes->getBoolean('_api_update');
    }

    private function isApiRespondEnabled(Request $request): bool
    {
        return $this->isApiRequest($request) && $request->attributes->getBoolean('_api_respond', true);
    }

    private function hasApiSetting(Request $request, string $key): bool
    {
        return $request->attributes->has('_api_'.$key);
    }

    private function getApiSetting(Request $request, string $key, mixed $default = null): mixed
    {
        return $request->attributes->get('_api_'.$key, $default);
    }
}
