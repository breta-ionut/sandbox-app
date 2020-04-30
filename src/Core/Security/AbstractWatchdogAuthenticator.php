<?php

declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Security\Token\WatchdogToken;
use App\Core\Security\Token\WatchdogTokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Provides default implementations for parts of the authenticator which rarely need to be customized.
 */
abstract class AbstractWatchdogAuthenticator implements WatchdogAuthenticatorInterface
{
    /**
     * {@inheritDoc}
     */
    public function createAuthenticatedToken(UserInterface $user, string $firewall): WatchdogTokenInterface
    {
        return new WatchdogToken($user, $firewall);
    }
}
