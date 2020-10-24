<?php

declare(strict_types=1);

namespace App\User\Security;

use App\Api\Http\ResponseFactory;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LogoutListener implements EventSubscriberInterface
{
    private ResponseFactory $responseFactory;

    /**
     * @param ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param LogoutEvent $event
     */
    public function onLogout(LogoutEvent $event): void
    {
        $event->setResponse($this->responseFactory->createFromData(null, Response::HTTP_NO_CONTENT));
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            LogoutEvent::class => ['onLogout', 64],
        ];
    }
}
