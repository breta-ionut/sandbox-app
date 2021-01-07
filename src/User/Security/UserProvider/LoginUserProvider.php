<?php

declare(strict_types=1);

namespace App\User\Security\UserProvider;

use App\User\Model\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class LoginUserProvider extends AbstractUserProvider
{
    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername(string $username): User
    {
        $user = $this->userRepository->findOneBy(['email' => $username]);
        if (null === $user) {
            throw new UsernameNotFoundException(\sprintf('No user with email "%s" found.', $username));
        }

        return $user;
    }
}
