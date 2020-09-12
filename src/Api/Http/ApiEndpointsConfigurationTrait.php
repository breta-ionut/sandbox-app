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
    public function isApiRequest(Request $request): bool
    {
        return $request->attributes->getBoolean('_api_endpoint');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isApiReceiveEnabled(Request $request): bool
    {
        return $this->isApiRequest($request) && $request->attributes->getBoolean('_api_receive');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isApiUpdateEnabled(Request $request): bool
    {
        return $this->isApiRequest($request) && $request->attributes->getBoolean('_api_update');
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    public function isApiRespondEnabled(Request $request): bool
    {
        return $this->isApiRequest($request) && $request->attributes->getBoolean('_api_respond', true);
    }

    /**
     * @param Request $request
     * @param string  $key
     *
     * @return mixed
     */
    public function getApiSetting(Request $request, string $key)
    {
        return $request->attributes->get('_api_'.$key);
    }
}
