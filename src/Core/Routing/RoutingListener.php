<?php

namespace App\Core\Routing;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * Matches incoming requests with application routes in order to determine the controllers responsible for handling
 * them.
 */
class RoutingListener
{
    /**
     * @var RequestMatcherInterface
     */
    private RequestMatcherInterface $requestMatcher;

    /**
     * @param RequestMatcherInterface $requestMatcher
     */
    public function __construct(RequestMatcherInterface $requestMatcher)
    {
        $this->requestMatcher = $requestMatcher;
    }

    /**
     * @param RequestEvent $event
     */
    public function onRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $request->attributes->add($this->requestMatcher->matchRequest($request));
    }
}
