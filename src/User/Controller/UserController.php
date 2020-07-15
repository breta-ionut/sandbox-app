<?php

declare(strict_types=1);

namespace App\User\Controller;

use App\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;

class UserController extends AbstractController
{
    /**
     * @param UserInterface $user
     *
     * @return JsonResponse
     */
    public function login(UserInterface $user): JsonResponse
    {
        return $this->json($user);
    }
}
