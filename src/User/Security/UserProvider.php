<?php

declare(strict_types=1);

namespace App\User\Security;

use App\User\Model\User;
use App\User\Repository\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    private UserRepository $repository;

    /**
     * @param UserRepository $repository
     */
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername(string $username)
    {
        $user = $this->repository->findOneBy(['email' => $username]);
        if (null === $user) {
            throw new UsernameNotFoundException(\sprintf('No user with email "%s" found.', $username));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(\sprintf('Users of type "%s" are not supported.', \get_class($user)));
        }

        $userId = $user->getId();
        $refreshedUser = $this->repository->find($userId);

        if (null === $refreshedUser) {
            throw new UsernameNotFoundException(\sprintf('No user with id "%d" found.', $userId));
        }

        return $refreshedUser;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass(string $class)
    {
        return User::class === $class;
    }
}
