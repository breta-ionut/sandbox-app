<?php

declare(strict_types=1);

namespace App\User\Repository;

use App\Core\Doctrine\ServiceEntityRepository;
use App\User\Model\User;
use Doctrine\ORM\EntityManagerInterface;

class UserRepository extends ServiceEntityRepository
{
    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, User::class);
    }
}
