<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\Api\Exception\ValidationException;
use App\Api\Http\View;
use App\Core\Controller\AbstractController;
use App\User\Model\User;
use App\User\User\UserManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(name: 'user_')]
class UserController extends AbstractController
{
    #[Route(name: 'get', methods: 'GET')]
    public function getUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    /**
     * @throws ValidationException
     */
    #[Route(name: 'register', methods: 'POST')]
    public function register(User $user, ValidatorInterface $validator, UserManager $userManager): View
    {
        $violations = $validator->validate($user);
        if (0 !== \count($violations)) {
            throw new ValidationException($violations);
        }

        $userManager->create($user);
        $userManager->authenticate($user);

        return new View($user, Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: 'POST')]
    public function login(UserInterface $user): UserInterface
    {
        return $user;
    }

    /**
     * @throws \BadMethodCallException
     */
    #[Route('/logout', name: 'logout', methods: 'DELETE')]
    public function logout(): void
    {
        throw new \BadMethodCallException('This controller should not get called.');
    }
}
