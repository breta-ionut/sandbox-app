<?php

declare(strict_types=1);

namespace App\Image\DependencyInjection;

use App\Image\Style\ImageStyleInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ImageExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(ImageStyleInterface::class)->addTag('app.image.style');
    }
}
