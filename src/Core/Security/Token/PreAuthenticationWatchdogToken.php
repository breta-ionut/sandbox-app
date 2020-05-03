<?php

declare(strict_types=1);

namespace App\Core\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Watchdog token used in the pre-authentication phase. Its role is to carry the credentials from the
 * WatchdogAuthenticationListener to the WatchdogAuthenticationProvider. If the authentication is successful, the
 * provider will create a new authenticated token, this one being disposed. As a consequence, this token is never
 * authenticated.
 */
class PreAuthenticationWatchdogToken extends AbstractToken implements WatchdogTokenInterface
{
    /**
     * @var mixed
     */
    private $credentials;

    private string $firewall;

    /**
     * @param mixed  $credentials
     * @param string $firewall
     */
    public function __construct($credentials, string $firewall)
    {
        parent::__construct();

        $this->credentials = $credentials;
        $this->firewall = $firewall;

        $this->setAuthenticated(false);
    }

    /**
     * {@inheritDoc}
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * {@inheritDoc}
     */
    public function setAuthenticated(bool $isAuthenticated)
    {
        throw new \BadMethodCallException('The PreAuthenticationWatchdogToken is never authenticated.');
    }

    /**
     * {@inheritDoc}
     */
    public function getFirewall(): string
    {
        return $this->firewall;
    }
}