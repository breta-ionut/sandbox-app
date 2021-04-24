<?php

declare(strict_types=1);

namespace App\Image\Repository;

use App\Core\Doctrine\ServiceEntityRepository;
use App\Image\Model\Image;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @method Image|null findOneByToken(string $token)
 */
class ImageRepository extends ServiceEntityRepository
{
    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, Image::class);
    }
}
