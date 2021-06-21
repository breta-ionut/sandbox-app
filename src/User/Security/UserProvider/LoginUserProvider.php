<?php

declare(strict_types=1);

namespace App\User\Security\UserProvider;

use App\User\Model\User;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class LoginUserProvider extends AbstractUserProvider
{
    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername(string $username): User
    {
        return $this->loadUserByIdentifier($username);
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByIdentifier(string $identifier): User
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
