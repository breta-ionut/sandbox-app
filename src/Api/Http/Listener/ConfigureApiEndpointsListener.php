<?php

declare(strict_types=1);

namespace App\Api\Http\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Configures request attributes for API endpoints.
 *
 * First of all, it detects API endpoints (by a specific path prefix, e.g. /api/) and marks them with the
 * "_api_endpoint" request attribute. One might set a specific value for the attribute (via the route's "defaults"
 * configuration) for endpoints not following the path prefix rule.
 */
class ConfigureApiEndpointsListener implements EventSubscriberInterface
{
    private string $apiPathPrefix;

    /**
     * @param string $apiPathPrefix
     */
    public function __construct(string $apiPathPrefix)
    {
        $this->apiPathPrefix = $apiPathPrefix;
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $requestAttributes = $request->attributes;

        if (0 !== \strpos($request->getPathInfo(), $this->apiPathPrefix)
            || !$requestAttributes->getBoolean('_api_endpoint', true)
        ) {
            return;
        }

        $requestAttributes->set('_api_endpoint', true);

        if (!$requestAttributes->has('_api_receive')) {
            $requestAttributes->set(
                '_api_receive',
                \in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_PATCH]),
            );
        }

        if (!$requestAttributes->has('_api_update')) {
            $requestAttributes->set(
                '_api_update',
                \in_array($request->getMethod(), [Request::METHOD_PUT, Request::METHOD_PATCH]),
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 16]];
    }
}
