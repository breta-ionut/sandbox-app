<?php

declare(strict_types=1);

namespace App\Api\Http\Listener;

use App\Api\Http\ApiEndpointsConfigurationTrait;
use App\Api\Http\ResponseFactory;
use App\Api\Http\View;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Converts data returned by API controllers to responses using serialization. The serialization can be disabled by
 * setting the "_api_respond" request attribute to false for an endpoint (via the route's "defaults" configuration).
 *
 * One might return from an API controller a {@see View} object which wraps the data and can provide additional
 * properties for the response (status, headers) or serialization settings (groups, context).
 *
 * For serialization, "api_response" group is used by default (provided by {@see ResponseFactory}).
 */
class SerializeListener implements EventSubscriberInterface
{
    use ApiEndpointsConfigurationTrait;

    public function __construct(private ResponseFactory $responseFactory)
    {
    }

    public function onKernelView(ViewEvent $event): void
    {
        if (!$this->isApiRespondEnabled($event->getRequest())) {
            return;
        }

        $controllerResult = $event->getControllerResult();

        if ($controllerResult instanceof View) {
            $response = $this->responseFactory->createFromData(
                $controllerResult->getData(),
                $controllerResult->getStatus(),
                $controllerResult->getHeaders(),
                $controllerResult->getSerializationContext(),
            );
        } else {
            $response = $this->responseFactory->createFromData($controllerResult);
        }

        $event->setResponse($response);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::VIEW => 'onKernelView'];
    }
}
