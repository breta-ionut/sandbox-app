<?php

declare(strict_types=1);

namespace App\Image\Image;

use App\Image\Model\Image;
use Doctrine\ORM\EntityManagerInterface;

class ImageManager
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function upload(Image $image): void
    {
        $this->entityManager->persist($image);
        $this->entityManager->flush();
    }
}
