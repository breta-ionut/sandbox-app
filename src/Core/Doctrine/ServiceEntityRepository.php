<?php

declare(strict_types=1);

namespace App\Core\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Base implementation for an entity repository which can be registered as a service.
 */
class ServiceEntityRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $entityManager, string $entityName)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata($entityName));
    }
}
