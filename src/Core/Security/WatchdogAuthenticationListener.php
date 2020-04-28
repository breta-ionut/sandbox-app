<?php

declare(strict_types=1);

namespace App\Core\Security;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\AbstractListener;

class WatchdogAuthenticationListener extends AbstractListener
{
    private string $firewall;
    private WatchdogAuthenticatorInterface $authenticator;
    private AuthenticationManagerInterface $authenticationManager;
    private WatchdogAuthenticationHelper $authenticationHelper;
    private LoggerInterface $logger;

    /**
     * @param string                         $firewall
     * @param WatchdogAuthenticatorInterface $authenticator
     * @param AuthenticationManagerInterface $authenticationManager
     * @param WatchdogAuthenticationHelper   $authenticationHelper
     * @param LoggerInterface|null           $logger
     */
    public function __construct(
        string $firewall,
        WatchdogAuthenticatorInterface $authenticator,
        AuthenticationManagerInterface $authenticationManager,
        WatchdogAuthenticationHelper $authenticationHelper,
        LoggerInterface $logger = null
    ) {
        $this->firewall = $firewall;
        $this->authenticator = $authenticator;
        $this->authenticationManager = $authenticationManager;
        $this->authenticationHelper = $authenticationHelper;
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * {@inheritDoc}
     */
    public function supports(Request $request): ?bool
    {
        $this->logger->debug(
            'Checking if watchdog authentication activates on current request.',
            ['firewall' => $this->firewall]
        );

        $supports = $this->authenticator->supports($request);

        if (!$supports) {
            $this->logger->debug(
                'Watchdog authentication doesn\'t activate on current request.',
                ['firewall' => $this->firewall]
            );
        }

        return $supports;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(RequestEvent $event)
    {
        $request = $event->getRequest();

        try {
            $this->logger->debug('Getting credentials from watchdog authenticator.', ['firewall' => $this->firewall]);

            $credentials = $this->authenticator->getCredentials($request);
            if (null === $credentials) {
                throw new \UnexpectedValueException(\sprintf(
                    'The result of "%s::getCredentials()" must be a non-null value.',
                    \get_class($this->authenticator)
                ));
            }

            $token = new PreAuthenticationWatchdogToken($credentials, $this->firewall);

            $this->logger->debug(
                'Passing the PreAuthenticationWatchdogToken with the credentials to the AuthenticationProviderManager.',
                ['firewall' => $this->firewall]
            );

            /** @var WatchdogTokenInterface $token */
            $token = $this->authenticationManager->authenticate($token);

            $this->logger->info(
                'Watchdog authentication successful.',
                ['firewall' => $this->firewall, 'token' => $token]
            );

            $this->authenticationHelper->authenticateWithToken($token, $request);
        } catch (AuthenticationException $exception) {
            $this->logger->info(
                'Watchdog authentication failed.',
                ['firewall' => $this->firewall, 'exception' => $exception]
            );

            $this->handleAuthenticationFailure($event, $exception);

            return;
        }

        $this->handleAuthenticationSuccess($event, $token);
    }

    /**
     * @param RequestEvent            $event
     * @param AuthenticationException $exception
     */
    private function handleAuthenticationFailure(RequestEvent $event, AuthenticationException $exception): void
    {
        $response = $this->authenticator->onAuthenticationFailure($event->getRequest(), $exception);

        if (null === $response) {
            $this->logger->debug(
                'Watchdog authenticator set no error response: request continues.',
                ['firewall' => $this->firewall]
            );

            return;
        }

        $this->logger->debug(
            'Watchdog authenticator set error response.',
            ['firewall' => $this->firewall, 'response' => $response]
        );

        $event->setResponse($response);
    }

    /**
     * @param RequestEvent           $event
     * @param WatchdogTokenInterface $token
     */
    private function handleAuthenticationSuccess(RequestEvent $event, WatchdogTokenInterface $token): void
    {
        $response = $this->authenticator->onAuthenticationSuccess($event->getRequest(), $token);

        if (null === $response) {
            $this->logger->debug(
                'Watchdog authenticator set no success response: request continues.',
                ['firewall' => $this->firewall]
            );

            return;
        }

        $this->logger->debug(
            'Watchdog authenticator set success response.',
            ['firewall' => $this->firewall, 'response' => $response]
        );

        $event->setResponse($response);
    }
}
