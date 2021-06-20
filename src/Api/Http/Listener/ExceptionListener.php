<?php

declare(strict_types=1);

namespace App\Api\Http\Listener;

use App\Api\Http\ApiEndpointsConfigurationTrait;
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
    use ApiEndpointsConfigurationTrait;

    public function __construct(private ErrorListener $wrappedExceptionListener)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if ($this->isApiRequest($event->getRequest())) {
            $this->wrappedExceptionListener->onKernelException($event);
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onKernelException', -128]];
    }
}
