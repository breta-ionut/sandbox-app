<?php

declare(strict_types=1);

namespace App\User\Security\Authenticator;

use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CredentialsInterface;

class NullCredentials implements CredentialsInterface
{
    /**
     * {@inheritDoc}
     */
    public function isResolved(): bool
    {
        return true;
    }
}
