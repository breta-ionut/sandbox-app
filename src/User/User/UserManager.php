<?php

declare(strict_types=1);

namespace App\User\User;

use App\User\Model\User;
use App\User\Security\Authenticator\LoginAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class UserManager
{
    private UserPasswordEncoderInterface $userPasswordEncoder;
    private EntityManagerInterface $entityManager;
    private UserAuthenticatorInterface $userAuthenticator;
    private LoginAuthenticator $authenticator;
    private RequestStack $requestStack;

    /**
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param EntityManagerInterface       $entityManager
     * @param UserAuthenticatorInterface   $userAuthenticator
     * @param LoginAuthenticator           $authenticator
     * @param RequestStack                 $requestStack
     */
    public function __construct(
        UserPasswordEncoderInterface $userPasswordEncoder,
        EntityManagerInterface $entityManager,
        UserAuthenticatorInterface $userAuthenticator,
        LoginAuthenticator $authenticator,
        RequestStack $requestStack
    ) {
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->entityManager = $entityManager;
        $this->userAuthenticator = $userAuthenticator;
        $this->authenticator = $authenticator;
        $this->requestStack = $requestStack;
    }

    /**
     * @param User $user
     */
    public function create(User $user): void
    {
        $password = $this->userPasswordEncoder->encodePassword($user, $user->getPlainPassword());
        $user->setPassword($password);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * @param User $user
     */
    public function authenticate(User $user): void
    {
        $this->userAuthenticator->authenticateUser(
            $user,
            $this->authenticator,
            $this->requestStack->getCurrentRequest()
        );
    }
}
