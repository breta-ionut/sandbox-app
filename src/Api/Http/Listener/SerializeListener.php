<?php

declare(strict_types=1);

namespace App\Api\Http\Listener;

use App\Api\Http\ApiEndpointsConfigurationTrait;
use App\Api\Http\ResponseFactory;
use App\Api\Http\View;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * Converts data returned by API controllers to responses using serialization. The serialization can be disabled by
 * setting the "_api_respond" request attribute to false for an endpoint (via the route's "defaults" configuration).
 *
 * One might return from an API controller a {@see View} object which wraps the data and can provide additional
 * properties for the response (status, headers) or serialization settings (groups).
 *
 * For serialization, "api_respond" group is used by default.
 */
class SerializeListener implements EventSubscriberInterface
{
    use ApiEndpointsConfigurationTrait;

    private const DEFAULT_SERIALIZATION_GROUPS = ['api_respond'];

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
            $response = $this->responseFactory->createFromData(
                $controllerResult->getData(),
                $controllerResult->getStatus(),
                $controllerResult->getHeaders(),
                [AbstractNormalizer::GROUPS => $this->getSerializationGroups($controllerResult)]
            );
        } else {
            $response = $this->responseFactory->createFromData(
                $controllerResult,
                Response::HTTP_OK,
                [],
                [AbstractNormalizer::GROUPS => self::DEFAULT_SERIALIZATION_GROUPS]
            );
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

    /**
     * @param View $view
     *
     * @return string[]
     */
    private function getSerializationGroups(View $view): array
    {
        return \array_merge($view->getSerializationGroups(), self::DEFAULT_SERIALIZATION_GROUPS);
    }
}
