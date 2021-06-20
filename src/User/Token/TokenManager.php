<?php

declare(strict_types=1);

namespace App\User\Token;

use App\User\Model\Token;
use App\User\Model\User;
use App\User\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;

class TokenManager
{
    /**
     * @param int $tokenAvailability In minutes.
     */
    public function __construct(
        private TokenRepository $repository,
        private EntityManagerInterface $entityManager,
        private int $tokenAvailability,
    ) {
    }

    public function getOrCreate(User $user): Token
    {
        if (null === ($token = $this->repository->findOneAvailableByUser($user))) {
            $token = $this->create($user);
        }

        $token->setExpiresAt(new \DateTime(\sprintf('+%d minutes', $this->tokenAvailability)));

        $this->entityManager->flush();

        return $token;
    }

    private function create(User $user): Token
    {
        $token = (new Token())->setUser($user);

        $this->entityManager->persist($token);

        return $token;
    }
}
