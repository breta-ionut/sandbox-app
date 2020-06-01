<?php

declare(strict_types=1);

namespace App\Core\EventDispatcher;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Event dispatcher which bubbles up events to a wrapped dispatcher.
 */
class BubblingEventDispatcher extends EventDispatcher
{
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();

        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     */
    public function dispatch(object $event, string $eventName = null): object
    {
        $event = parent::dispatch($event, $eventName);

        return $this->eventDispatcher->dispatch($event, $eventName);
    }
}
