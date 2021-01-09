<?php

declare(strict_types=1);

namespace App\User\Security\UserProvider;

use App\User\Model\User;
use App\User\Repository\TokenRepository;
use App\User\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class TokenUserProvider extends AbstractUserProvider
{
    private TokenRepository $tokenRepository;

    /**
     * @param UserRepository  $userRepository
     * @param TokenRepository $tokenRepository
     */
    public function __construct(UserRepository $userRepository, TokenRepository $tokenRepository)
    {
        parent::__construct($userRepository);

        $this->tokenRepository = $tokenRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername(string $username): User
    {
        $token = $this->tokenRepository->findOneAvailableByToken($username);
        if (null === $token) {
            throw new UsernameNotFoundException(\sprintf('Token "%s" does not exist or is expired.', $username));
        }

        $user = $token->getUser();
        $user->setCurrentToken($token);

        return $user;
    }
}
