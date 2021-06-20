<?php

declare(strict_types=1);

namespace App\Image\Style;

use App\Image\Model\ImageContent;
use Imagine\Image\ImagineInterface;
use Psr\Container\ContainerInterface;

class ImageStyler
{
    public function __construct(private ImagineInterface $imagine, private ContainerInterface $imageStylesLocator)
    {
    }

    /**
     * @throws \DomainException
     */
    public function apply(ImageContent $imageContent, string $style): ImageContent
    {
        if (!$this->imageStylesLocator->has($style)) {
            throw new \DomainException(\sprintf('Unknown image style "%s".', $style));
        }

        $image = $this->imagine->load((string) $imageContent);

        $this->imageStylesLocator
            ->get($style)
            ->apply($image);

        return new ImageContent($image->get($imageContent->getFormat()));
    }
}
