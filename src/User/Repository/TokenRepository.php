<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\Core\Doctrine\ServiceEntityRepository;
use App\User\Model\Token;
use App\User\Model\User;
use Doctrine\ORM\EntityManagerInterface;

class TokenRepository extends ServiceEntityRepository
{
    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Token::class);
    }

    /**
     * @param User $user
     *
     * @return Token|null
     */
    public function findOneAvailableByUser(User $user): ?Token
    {
        return $this->createQueryBuilder('t')
            ->where('t.user = :user')
            ->andWhere('t.expiresAt <= CURRENT_TIMESTAMP()')
            ->setMaxResults(1)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
