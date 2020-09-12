<?php

declare(strict_types=1);

namespace App\User\Model;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationError
{
    private ?AuthenticationException $exception;

    /**
     * @param AuthenticationException|null $exception
     */
    public function __construct(?AuthenticationException $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @return bool
     */
    public function hasException(): bool
    {
        return null !== $this->exception;
    }

    /**
     * @return AuthenticationException|null
     */
    public function getException(): ?AuthenticationException
    {
        return $this->exception;
    }
}
