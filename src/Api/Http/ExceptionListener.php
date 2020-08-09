<?php

declare(strict_types=1);

namespace App\Api\Http;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;

class ExceptionListener implements EventSubscriberInterface
{
    private SerializerInterface $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function renderException(ExceptionEvent $event): void
    {
        $data = $this->serializer->serialize($event->getThrowable(), 'json', ['api_response' => true]);

        $event->setResponse(
            new JsonResponse($data, Response::HTTP_OK, ['Content-Type' => 'application/problem+json'], true)
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [KernelEvents::EXCEPTION => ['renderException', -1024]];
    }
}
