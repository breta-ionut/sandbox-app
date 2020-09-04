<?php

declare(strict_types=1);

namespace App\Api\Http\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Delegates handling of exceptions caught on API endpoints to a Symfony {@see ErrorListener} implementation which is
 * configured to use a custom API error controller.
 */
class ExceptionListener implements EventSubscriberInterface
{
    private ErrorListener $wrappedExceptionListener;

    /**
     * @param ErrorListener $wrappedExceptionListener
     */
    public function __construct(ErrorListener $wrappedExceptionListener)
    {
        $this->wrappedExceptionListener = $wrappedExceptionListener;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event): void
    {
        if ($event->getRequest()->attributes->getBoolean('_api_endpoint')) {
            $this->wrappedExceptionListener->onKernelException($event);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => ['onKernelException', -128]];
    }
}
