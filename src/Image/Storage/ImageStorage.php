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
        $path = $this->getPath($image);
        $image->setPath($path);

        $this->privateFilesystem->write($path, (string) $image->getContent());
    }

    /**
     * @param Image $image
     */
    public function delete(Image $image): void
    {
        $this->privateFilesystem->delete($image->getPath());
    }

    /**
     * @param Image $image
     *
     * @return string
     */
    private function getPath(Image $image): string
    {
        return \sprintf(
            '/images/%s/%s.%s',
            $this->getDateDirectory($image->getCreatedAt()),
            $image->getToken(),
            $image->getContent()->getFormat()
        );
    }

    /**
     * @param \DateTime $date
     *
     * @return string
     */
    private function getDateDirectory(\DateTime $date): string
    {
        return $date->format('Y-m-d');
    }

    /**
     * @param Image       $image
     * @param string|null $style
     *
     * @return string
     */
    private function getPublicPath(Image $image, string $style = null): string
    {
        return \sprintf(
            '/images/%s/%s%s',
            $this->getDateDirectory($image->getCreatedAt()),
            null !== $style ? "/$style" : '',
            \basename($image->getPath())
        );
    }
}
