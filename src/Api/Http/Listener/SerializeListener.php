<?php

declare(strict_types=1);

namespace App\Api\Http\Listener;

use App\Api\Http\ApiEndpointsConfigurationTrait;
use App\Api\Http\ResponseFactory;
use App\Api\Http\View;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

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
        if (!$this->isApiRespondEnabled($event->getRequest())) {
            return;
        }

        $controllerResult = $event->getControllerResult();

        if ($controllerResult instanceof View) {
            $context = [AbstractNormalizer::GROUPS => $controllerResult->getSerializationGroups()]
                + $controllerResult->getSerializationContext();

            $response = $this->responseFactory->createFromData(
                $controllerResult->getData(),
                $controllerResult->getStatus(),
                $controllerResult->getHeaders(),
                $context,
            );
        } else {
            $response = $this->responseFactory->createFromData($controllerResult);
        }

        $event->setResponse($response);
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => 'onKernelView'];
    }
}
