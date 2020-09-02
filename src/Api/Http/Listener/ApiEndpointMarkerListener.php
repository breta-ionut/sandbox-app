<?php

declare(strict_types=1);

namespace App\Api\Http\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiEndpointMarkerListener implements EventSubscriberInterface
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
            && false !== $request->attributes->get('_api_endpoint')
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
