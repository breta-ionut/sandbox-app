<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\Api\Exception\ValidationException;
use App\Api\Http\View;
use App\Core\Controller\AbstractController;
use App\User\Model\User;
use App\User\User\UserManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    /**
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    public function getUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    /**
     * @param User               $user
     * @param ValidatorInterface $validator
     * @param UserManager        $userManager
     *
     * @return View
     *
     * @throws ValidationException
     */
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

    /**
     * @param UserInterface $user
     *
     * @return UserInterface
     */
    public function login(UserInterface $user): UserInterface
    {
        return $user;
    }
}
