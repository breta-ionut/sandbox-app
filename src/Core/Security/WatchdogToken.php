<?php

declare(strict_types=1);

namespace App\Core\Security;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * The default authenticated watchdog token.
 */
class WatchdogToken extends AbstractToken implements WatchdogTokenInterface
{
    private string $firewall;

    /**
     * @param UserInterface $user
     * @param string        $firewall
     * @param string[]      $roles
     */
    public function __construct(UserInterface $user, string $firewall, array $roles = [])
    {
        parent::__construct($roles);

        $this->setUser($user);
        $this->firewall = $firewall;

        $this->setAuthenticated(true);
    }

    /**
     * {@inheritDoc}
     */
    public function getCredentials()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function __serialize(): array
    {
        return [$this->firewall, parent::__serialize()];
    }

    /**
     * {@inheritDoc}
     */
    public function __unserialize(array $data): void
    {
        [$this->firewall, $parentData] = $data;

        parent::__unserialize($parentData);
    }

    /**
     * {@inheritDoc}
     */
    public function getFirewall(): string
    {
        return $this->firewall;
    }
}
