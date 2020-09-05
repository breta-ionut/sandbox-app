<?php

declare(strict_types=1);

namespace App\Api\Http\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Marks API endpoints requests by setting the "_api_endpoint" request attribute.
 *
 * API endpoints are usually identified by a specific path prefix (e.g. /api/). One might manually set a value for the
 * "_api_endpoint" request attribute (via the route's "defaults" configuration) for endpoints not following this rule.
 */
class MarkApiEndpointsListener implements EventSubscriberInterface
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
    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (0 === \strpos($request->getPathInfo(), $this->apiPathPrefix)
            && !$request->attributes->getBoolean('_api_endpoint', true)
        ) {
            $request->attributes->set('_api_endpoint', true);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::REQUEST => ['onRequest', 16]];
    }
}
