<?php

declare(strict_types=1);

namespace App\Core\Security;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
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
}
