<?php

declare(strict_types=1);

namespace App\Api\Http\Listener;

use App\Api\Http\ApiEndpointsConfigurationTrait;
use App\Api\Http\ResponseFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Converts data returned by API controllers to responses using serialization. The serialization can be disabled by
 * setting the "_api_respond" request attribute to false for an endpoint (via the route's "defaults" configuration).
 */
class SerializeListener implements EventSubscriberInterface
{
    use ApiEndpointsConfigurationTrait;

    private ResponseFactory $responseFactory;

    /**
     * @param ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param ViewEvent $event
     */
    public function onKernelView(ViewEvent $event): void
    {
        if ($this->isApiRespondEnabled($event->getRequest())) {
            $response = $this->responseFactory->createFromData($event->getControllerResult());

            $event->setResponse($response);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => 'onKernelView'];
    }
}
