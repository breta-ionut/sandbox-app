<?php

declare(strict_types=1);

namespace App\Api\Http\Listener;

use App\Api\Http\ApiEndpointsConfigurationTrait;
use App\Api\Http\RequestReader;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * Populates Doctrine entities passed to controllers with data from API requests content.
 *
 * The deserialization can be disabled by setting the "_api_update" request attribute to false for an endpoint (via the
 * route's "defaults" configuration).
 */
class DeserializeListener implements EventSubscriberInterface
{
    use ApiEndpointsConfigurationTrait;

    private EntityManagerInterface $entityManager;
    private RequestReader $requestReader;

    /**
     * @param EntityManagerInterface $entityManager
     * @param RequestReader          $requestReader
     */
    public function __construct(EntityManagerInterface $entityManager, RequestReader $requestReader)
    {
        $this->entityManager = $entityManager;
        $this->requestReader = $requestReader;
    }

    /**
     * @param ControllerArgumentsEvent $event
     */
    public function onKernelControllerArguments(ControllerArgumentsEvent $event): void
    {
        $request = $event->getRequest();

        if (!$this->isApiUpdateEnabled($request)) {
            return;
        }

        foreach ($event->getArguments() as $argument) {
            if (\is_object($argument) && $this->entityManager->contains($argument)) {
                $this->requestReader->read(
                    $request,
                    ClassUtils::getClass($argument),
                    [AbstractNormalizer::OBJECT_TO_POPULATE => $argument]
                );

                // The request content can be used to populate a single entity, therefore we stop at the first one.
                return;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::CONTROLLER_ARGUMENTS => 'onKernelControllerArguments'];
    }
}
