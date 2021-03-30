<?php

declare(strict_types=1);

namespace App;

use App\Core\Kernel as CoreKernel;
use App\Image\DependencyInjection\ImageExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Kernel extends CoreKernel
{
    public const APP_NAME = 'Sandbox';
    public const APP_VERSION = '0.0.1';

    /**
     * {@inheritDoc}
     */
    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->registerExtension(new ImageExtension());
    }
}
