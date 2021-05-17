<?php

declare(strict_types=1);

namespace App\Api\Http;

use Symfony\Component\HttpFoundation\Request;

trait ApiEndpointsConfigurationTrait
{
    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isApiRequest(Request $request): bool
    {
        return $request->attributes->getBoolean('_api_endpoint');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isApiReceiveEnabled(Request $request): bool
    {
        return $this->isApiRequest($request) && $request->attributes->getBoolean('_api_receive');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isApiUpdateEnabled(Request $request): bool
    {
        return $this->isApiRequest($request) && $request->attributes->getBoolean('_api_update');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isApiRespondEnabled(Request $request): bool
    {
        return $this->isApiRequest($request) && $request->attributes->getBoolean('_api_respond', true);
    }

    /**
     * @param Request $request
     * @param string  $key
     *
     * @return bool
     */
    private function hasApiSetting(Request $request, string $key): bool
    {
        return $request->attributes->has('_api_'.$key);
    }

    /**
     * @param Request $request
     * @param string  $key
     * @param mixed   $default
     *
     * @return mixed
     */
    private function getApiSetting(Request $request, string $key, mixed $default = null): mixed
    {
        return $request->attributes->get('_api_'.$key, $default);
    }
}
