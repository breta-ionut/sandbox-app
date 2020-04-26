<?php

declare(strict_types=1);

namespace App\Core\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class WatchdogAuthenticationHelper
{
    private TokenStorageInterface $tokenStorage;
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @var string[]
     */
    private array $statelessFirewalls;

    private SessionAuthenticationStrategyInterface $sessionAuthenticationStrategy;

    /**
     * @param TokenStorageInterface                  $tokenStorage
     * @param EventDispatcherInterface               $eventDispatcher
     * @param string[]                               $statelessFirewalls
     * @param SessionAuthenticationStrategyInterface $sessionAuthenticationStrategy
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        array $statelessFirewalls,
        SessionAuthenticationStrategyInterface $sessionAuthenticationStrategy
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->statelessFirewalls = $statelessFirewalls;
        $this->sessionAuthenticationStrategy = $sessionAuthenticationStrategy;
    }

    /**
     * @param WatchdogTokenInterface $token
     * @param Request                $request
     */
    public function authenticateWithToken(WatchdogTokenInterface $token, Request $request): void
    {
        if (!\in_array($token->getFirewall(), $this->statelessFirewalls, true) && $request->hasPreviousSession()) {
            $this->sessionAuthenticationStrategy->onAuthentication($request, $token);
        }

        $this->tokenStorage->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);
    }

    /**
     * @param UserInterface                  $user
     * @param string                         $firewall
     * @param WatchdogAuthenticatorInterface $authenticator
     * @param Request                        $request
     *
     * @return Response|null
     */
    public function authenticateWithUser(
        UserInterface $user,
        string $firewall,
        WatchdogAuthenticatorInterface $authenticator,
        Request $request
    ): ?Response {
        $token = $authenticator->createAuthenticatedToken($user, $firewall);

        $this->authenticateWithToken($token, $request);

        return $authenticator->onAuthenticationSuccess($request, $token);
    }
}
