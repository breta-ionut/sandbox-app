<?php

declare(strict_types=1);

namespace App\Core\Doctrine;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager as BaseEventManager;
use Psr\Container\ContainerInterface;

/**
 * Event manager which allows lazy-loading of listeners which are also services.
 */
class EventManager extends BaseEventManager
{
    private array $listeners = [];
    private array $initialized = [];

    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function dispatchEvent($eventName, ?EventArgs $eventArgs = null): void
    {
        if (!$this->hasListeners($eventName)) {
            return;
        }

        $eventArgs = $eventArgs ?? EventArgs::getEmptyInstance();
        $this->initializeListeners($eventName);

        foreach ($this->listeners[$eventName] as $listener) {
            \call_user_func([$listener, $eventName], $eventArgs);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getListeners($event = null): array
    {
        if (null === $event) {
            foreach (\array_keys($this->listeners) as $event) {
                $this->initializeListeners($event);
            }

            return $this->listeners;
        }

        if (!$this->hasListeners($event)) {
            return [];
        }

        $this->initializeListeners($event);

        return $this->listeners[$event];
    }

    /**
     * {@inheritDoc}
     */
    public function hasListeners($event): bool
    {
        return !empty($this->listeners[$event]);
    }

    /**
     * {@inheritDoc}
     */
    public function addEventListener($events, $listener): void
    {
        $hash = $this->getHash($listener);

        foreach ((array) $events as $event) {
            $this->listeners[$event][$hash] = $listener;

            if (\is_string($listener)) {
                $this->initialized[$event] = false;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeEventListener($events, $listener): void
    {
        $hash = $this->getHash($listener);

        foreach ((array) $events as $event) {
            unset($this->listeners[$event][$hash]);
        }
    }

    private function initializeListeners(string $event): void
    {
        if (!empty($this->initialized[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $hash => $listener) {
            if (\is_string($listener)) {
                $this->listeners[$event][$hash] = $this->container->get($listener);
            }
        }

        $this->initialized[$event] = true;
    }

    private function getHash(string|object $listener): string
    {
        return \is_string($listener) ? 'service:'.$listener : \spl_object_hash($listener);
    }
}
