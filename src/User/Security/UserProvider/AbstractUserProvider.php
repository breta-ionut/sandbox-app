<?php

declare(strict_types=1);

namespace App\User\Security\UserProvider;

use App\User\Model\User;
use App\User\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

abstract class AbstractUserProvider implements UserProviderInterface
{
    public function __construct(protected UserRepository $userRepository)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user): User
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(\sprintf('Users of type "%s" are not supported.', \get_class($user)));
        }

        $email = $user->getEmail();
        $refreshedUser = $this->userRepository->findOneBy(['email' => $email]);

        if (null === $refreshedUser) {
            $exception = new UserNotFoundException(\sprintf('No user with email "%s" found.', $email));
            $exception->setUserIdentifier($email);

            throw $exception;
        }

        return $refreshedUser;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass(string $class): bool
    {
        return User::class === $class;
    }
}
