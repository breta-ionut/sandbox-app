<?php

declare(strict_types=1);

namespace App\Image\Style;

use Imagine\Image\ImageInterface;

interface ImageStyleInterface
{
    /**
     * @param ImageInterface $image
     */
    public function apply(ImageInterface $image): void;

    /**
     * @return string
     */
    public static function getName(): string;
}
