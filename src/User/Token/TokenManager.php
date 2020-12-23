<?php

declare(strict_types=1);

namespace App\User\Token;

use App\User\Model\Token;
use App\User\Model\User;
use App\User\Repository\TokenRepository;
use Doctrine\ORM\EntityManagerInterface;

class TokenManager
{
    private const TOKEN_LENGTH = 64;

    private TokenRepository $repository;
    private EntityManagerInterface $entityManager;

    /**
     * @var int In minutes.
     */
    private int $tokenAvailability;

    /**
     * @param TokenRepository        $repository
     * @param EntityManagerInterface $entityManager
     * @param int                    $tokenAvailability
     */
    public function __construct(
        TokenRepository $repository,
        EntityManagerInterface $entityManager,
        int $tokenAvailability
    ) {
        $this->repository = $repository;
        $this->entityManager = $entityManager;
        $this->tokenAvailability = $tokenAvailability;
    }

    /**
     * @param User $user
     *
     * @return Token
     */
    public function getOrCreate(User $user): Token
    {
        if (null === ($token = $this->repository->findOneAvailableByUser($user))) {
            $token = $this->create($user);
        }

        $token->setExpiresAt(new \DateTime(\sprintf('+%d minutes', $this->tokenAvailability)));

        $this->entityManager->flush();

        return $token;
    }

    /**
     * @param User $user
     *
     * @return Token
     */
    private function create(User $user): Token
    {
        $token = (new Token())
            ->setToken(\bin2hex(\random_bytes(self::TOKEN_LENGTH)))
            ->setUser($user);

        $this->entityManager->persist($token);

        return $token;
    }
}
