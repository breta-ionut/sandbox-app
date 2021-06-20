<?php

declare(strict_types=1);

namespace App\Image\Storage;

use App\Common\Filesystem\PublicFilesystemOperator;
use App\Image\Model\Image;
use App\Image\Model\ImageContent;
use App\Image\Style\ImageStyler;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ImageStorage
{
    public function __construct(
        private FilesystemOperator $privateFilesystem,
        private PublicFilesystemOperator $publicFilesystem,
        private ImageStyler $imageStyler,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function upload(Image $image): void
    {
        $path = $this->getPath($image);
        $image->setPath($path);

        $this->privateFilesystem->write($path, (string) $image->getContent());
    }

    public function delete(Image $image): void
    {
        $this->privateFilesystem->delete($image->getPath());
    }

    public function publish(Image $image, string $style = null): void
    {
        $imageContent = new ImageContent($this->privateFilesystem->read($image->getPath()));

        if (null !== $style) {
            $imageContent = $this->imageStyler->apply($imageContent, $style);
        }

        $this->publicFilesystem->write($this->getPublicPath($image, $style), (string) $imageContent);
    }

    /**
     * @param string[] $forStyles
     */
    public function setPublicUrls(Image $image, bool $original = false, array $forStyles = []): void
    {
        if ($original) {
            $image->setOriginalPublicUrl($this->getPublicUrl($image));
        }

        foreach ($forStyles as $style) {
            $image->setPublicUrlForStyle($style, $this->getPublicUrl($image, $style));
        }
    }

    private function getPath(Image $image): string
    {
        return \sprintf(
            '/images/%s/%s.%s',
            $this->getDateDirectory($image->getCreatedAt()),
            $image->getToken(),
            $image->getContent()->getFormat()
        );
    }

    private function getDateDirectory(\DateTime $date): string
    {
        return $date->format('Y-m-d');
    }

    private function getPublicPath(Image $image, ?string $style): string
    {
        return \sprintf(
            '/images/%s/%s%s',
            $this->getDateDirectory($image->getCreatedAt()),
            null !== $style ? "$style/" : '',
            \basename($image->getPath())
        );
    }

    private function getPublicUrl(Image $image, string $style = null): string
    {
        $publicPath = $this->getPublicPath($image, $style);

        if ($this->publicFilesystem->fileExists($publicPath)) {
            return $this->publicFilesystem->publicUrl($publicPath);
        }

        return $this->generatePublicUrl($image, $style);
    }

    private function generatePublicUrl(Image $image, ?string $style): string
    {
        $parameters = ['token' => $image->getToken()] + (null !== $style ? \compact($style) : []);

        return $this->urlGenerator->generate('app_image_image_get', $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
