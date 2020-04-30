<?php

declare(strict_types=1);

namespace App\Core\Security\Token;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Contract to be implemented by all tokens created and used throughout the watchdog authentication system.
 */
interface WatchdogTokenInterface extends TokenInterface
{
    /**
     * Returns the name of the firewall under which the token was created.
     *
     * @return string
     */
    public function getFirewall(): string;
}
