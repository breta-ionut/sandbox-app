<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\Core\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
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
