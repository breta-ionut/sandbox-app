<?php

declare(strict_types=1);

namespace App\Image\Storage;

use App\Common\Filesystem\PublicFilesystemOperator;
use App\Image\Model\Image;
use App\Image\Style\ImageStyler;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ImageStorage
{
    private FilesystemOperator $privateFilesystem;
    private PublicFilesystemOperator $publicFilesystem;
    private ImageStyler $imageStyler;
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @param FilesystemOperator       $privateFilesystem
     * @param PublicFilesystemOperator $publicFilesystem
     * @param ImageStyler              $imageStyler
     * @param UrlGeneratorInterface    $urlGenerator
     */
    public function __construct(
        FilesystemOperator $privateFilesystem,
        PublicFilesystemOperator $publicFilesystem,
        ImageStyler $imageStyler,
        UrlGeneratorInterface $urlGenerator,
    ) {
        $this->privateFilesystem = $privateFilesystem;
        $this->publicFilesystem = $publicFilesystem;
        $this->imageStyler = $imageStyler;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Image $image
     */
    public function upload(Image $image): void
    {
    }
}
