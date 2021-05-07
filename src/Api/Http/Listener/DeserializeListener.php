<?php

declare(strict_types=1);

namespace App\Api\Http\Listener;

use App\Api\Http\ApiEndpointsConfigurationTrait;
use App\Api\Http\RequestReader;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

/**
 * Populates Doctrine entities passed to controllers with data from API requests content.
 *
 * The deserialization can be disabled by setting the "_api_update" request attribute to false for an endpoint (via the
 * route's "defaults" configuration).
 *
 * Other request attributes used as configuration options:
 *      - "_api_update_argument": explicitly specifies the name of the argument to be updated. If not specified, the
 *        first argument eligible for update will be considered
 *      - "_api_update_deserialization_groups": additional groups to use when deserializing requests. The default
 *        deserialization group is "api_request" (provided by {@see RequestReader})
 *      - "_api_update_deserialization_context": additional context to pass when deserializing requests
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

        $arguments = $this->getArgumentsToCheck($request, $event->getController(), $event->getArguments());

        foreach ($arguments as $argument) {
            if (\is_object($argument) && $this->entityManager->contains($argument)) {
                $context = [
                    AbstractObjectNormalizer::OBJECT_TO_POPULATE => $argument,
                    AbstractObjectNormalizer::GROUPS => $this->getApiSetting(
                        $request,
                        'update_deserialization_groups',
                        [],
                    ),
                ] + $this->getApiSetting($request, 'update_deserialization_context', []) + [
                    AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
                ];

                $this->requestReader->read($request, ClassUtils::getClass($argument), $context);

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

    /**
     * @param Request  $request
     * @param callable $controller
     * @param array    $arguments
     *
     * @return array
     *
     * @throws \LogicException
     */
    private function getArgumentsToCheck(Request $request, callable $controller, array $arguments): array
    {
        if (!$this->hasApiSetting($request, 'update_argument')) {
            return $arguments;
        }

        $parametersReflection = (new \ReflectionFunction(\Closure::fromCallable($controller)))->getParameters();
        $argumentToUpdateName = $this->getApiSetting($request, 'update_argument');

        foreach ($parametersReflection as $index => $reflection) {
            if ($reflection->getName() !== $argumentToUpdateName) {
                continue;
            }

            return \array_key_exists($index, $arguments) ? [$arguments[$index]] : [];
        }

        throw new \LogicException(\sprintf('No argument to update with name "%s" found.', $argumentToUpdateName));
    }
}
