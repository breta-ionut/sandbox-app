<?php

declare(strict_types=1);

namespace App\User\Security\UserProvider;

use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginUserProvider extends AbstractUserProvider
{
    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userRepository->findOneBy(['email' => $identifier]);
        if (null === $user) {
            $exception = new UserNotFoundException(\sprintf('No user with email "%s" found.', $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return $user;
    }
}
