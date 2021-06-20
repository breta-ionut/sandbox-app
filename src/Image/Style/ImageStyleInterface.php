<?php

declare(strict_types=1);

namespace App\Image\Style;

use Imagine\Image\ImageInterface;

interface ImageStyleInterface
{
    public function apply(ImageInterface $image): void;

    public static function getName(): string;
}
