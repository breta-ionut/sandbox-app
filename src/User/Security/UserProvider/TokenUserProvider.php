<?php

declare(strict_types=1);

namespace App\User\Security\UserProvider;

use App\User\Model\User;
use App\User\Repository\TokenRepository;
use App\User\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class TokenUserProvider extends AbstractUserProvider
{
    public function __construct(private TokenRepository $tokenRepository, UserRepository $userRepository)
    {
        parent::__construct($userRepository);
    }

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
        $token = $this->tokenRepository->findOneAvailableByToken($identifier);
        if (null === $token) {
            $exception = new UserNotFoundException(\sprintf('Token "%s" does not exist or is expired.', $identifier));
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        $user = $token->getUser();
        $user->setCurrentToken($token);

        return $user;
    }
}
