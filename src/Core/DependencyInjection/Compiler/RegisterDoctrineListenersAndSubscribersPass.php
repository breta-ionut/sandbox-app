<?php

namespace App\Core\DependencyInjection\Compiler;

use App\Core\Doctrine\EventManager;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class RegisterDoctrineListenersAndSubscribersPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public const SUBSCRIBER_TAG = 'doctrine.event_subscriber';

    private const EVENT_MANAGER_ID = EventManager::class;
    private const LISTENER_TAG = 'doctrine.event_listener';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $eventManager = $container->getDefinition(self::EVENT_MANAGER_ID);
        $parameterBag = $container->getParameterBag();

        // Collect and register the event subscribers.
        foreach ($this->findAndSortTaggedServices(self::SUBSCRIBER_TAG, $container) as $subscriber) {
            $class = $parameterBag->resolveValue($container->getDefinition((string) $subscriber));
            $reflection = new \ReflectionClass($class);
            if (!$reflection->implementsInterface(EventSubscriber::class)) {
                throw new LogicException(sprintf(
                    '`%s` was registered as a Doctrine event subscriber but doesn\'t implement `%s`.',
                    $subscriber,
                    EventSubscriber::class
                ));
            }

            $eventManager->addMethodCall('addEventSubscriber', [$subscriber]);
        }

        // Collect the listeners.
        $listeners = [];
        foreach ($container->findTaggedServiceIds(self::LISTENER_TAG) as $id => $attributes) {
            if (!isset($attributes[0]['event'])) {
                throw new LogicException(sprintf('No event(s) specified for Doctrine event listener `%s`.', $id));
            }

            $listeners[$attributes[0]['priority'] ?? 0][$id] = (array) $attributes[0]['event'];
        }

        if (!$listeners) {
            return;
        }

        // Sort the listeners in order to have them registered according to their priorities.
        krsort($listeners);
        $listeners = array_merge(...$listeners);

        $listenersRefs = [];
        foreach ($listeners as $id => $events) {
            $eventManager->addMethodCall('addEventListener', [$events, $id]);

            $listenersRefs[$id] = new Reference($id);
        }

        // Build and inject a service locator containing the listeners for having them lazily loaded at runtime.
        $eventManager->setArgument('$container', ServiceLocatorTagPass::register($container, $listenersRefs));
    }
}
