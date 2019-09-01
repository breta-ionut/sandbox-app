<?php

namespace App\Core\Routing;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;

class RequestContextFactory
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @return RequestContext
     */
    public function factory(): RequestContext
    {
        $requestContext = new RequestContext();
        $request = $this->requestStack->getMasterRequest();

        return null !== $request ? $requestContext->fromRequest($request) : $requestContext;
    }
}
