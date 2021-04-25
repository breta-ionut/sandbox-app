<?php

declare(strict_types=1);

namespace App\Image\Image;

use App\Image\Model\Image;
use Doctrine\ORM\EntityManagerInterface;

class ImageManager
{
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Image $image
     */
    public function upload(Image $image): void
    {
        $this->entityManager->persist($image);
        $this->entityManager->flush();
    }
}
