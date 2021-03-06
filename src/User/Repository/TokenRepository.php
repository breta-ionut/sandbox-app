<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\Core\Doctrine\ServiceEntityRepository;
use App\User\Model\Token;
use App\User\Model\User;
use Doctrine\ORM\EntityManagerInterface;

class TokenRepository extends ServiceEntityRepository
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Token::class);
    }

    public function findOneAvailableByToken(string $token): ?Token
    {
        return $this->createQueryBuilder('t')
            ->where('t.token = :token')
            ->andWhere('t.expiresAt > CURRENT_TIMESTAMP()')
            ->setMaxResults(1)
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneAvailableByUser(User $user): ?Token
    {
        return $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.expiresAt > CURRENT_TIMESTAMP()')
            ->setMaxResults(1)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
