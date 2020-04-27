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
    private ContainerInterface $container;
    private array $listeners = [];
    private array $initialized = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatchEvent($eventName, ?EventArgs $eventArgs = null)
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
    public function getListeners($event = null)
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
    public function hasListeners($event)
    {
        return !empty($this->listeners[$event]);
    }

    /**
     * {@inheritDoc}
     */
    public function addEventListener($events, $listener)
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
    public function removeEventListener($events, $listener)
    {
        $hash = $this->getHash($listener);

        foreach ((array) $events as $event) {
            unset($this->listeners[$event][$hash]);
        }
    }

    /**
     * @param string $event
     */
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

    /**
     * @param string|object $listener
     *
     * @return string
     */
    private function getHash($listener): string
    {
        return \is_string($listener) ? 'service:'.$listener : \spl_object_hash($listener);
    }
}
