<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Image\Style\ImageStyler;
use Imagine\Gd\Imagine;
use Imagine\Image\ImagineInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\Image\\', '../../src/Image/*')
        ->exclude('../../src/Image/{Model}');

    $services->load('App\\Image\\Controller\\', '../../src/Image/Controller')
        ->tag('controller.service_arguments');

    $services->set(Imagine::class);
    $services->alias(ImagineInterface::class, Imagine::class);

    $services->set(ImageStyler::class)
        ->arg('$imageStylesLocator', tagged_locator('app.image.style', 'name', 'getName'));
};
