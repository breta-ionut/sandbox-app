<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Common\Filesystem\Adapter\PublicLocalFilesystemAdapter;
use App\Common\Filesystem\PublicFilesystem;
use App\Common\Filesystem\PublicFilesystemAdapter;
use App\Common\Filesystem\PublicFilesystemOperator;
use League\Flysystem\Config;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Visibility;
use Symfony\Component\DependencyInjection\ServiceLocator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('env(PUBLIC_FILESYSTEM)', 'local')
        ->set('env(PRIVATE_FILESYSTEM)', 'local');

    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\Common\\', '../../src/Common/*');

    $services->set(PublicLocalFilesystemAdapter::class)
        ->arg('$location', param('kernel.project_dir').'/public');

    $services->set('app.common.filesystem.public_adapter_locator', ServiceLocator::class)
        ->args([['local' => service(PublicLocalFilesystemAdapter::class)]]);

    $services->set(LocalFilesystemAdapter::class)
        ->args([param('kernel.project_dir').'/upload']);

    $services->set('app.common.filesystem.private_adapter_locator', ServiceLocator::class)
        ->args([['local' => service(LocalFilesystemAdapter::class)]]);

    $services->set(PublicFilesystemAdapter::class)
        ->factory([service('app.common.filesystem.public_adapter_locator'), 'get'])
        ->args([param('env(PUBLIC_FILESYSTEM)')]);

    $services->set(FilesystemAdapter::class)
        ->factory([service('app.common.filesystem.private_adapter_locator'), 'get'])
        ->args([param('env(PRIVATE_FILESYSTEM)')]);

    $services->set(PublicFilesystem::class)
        ->args([
            service(PublicFilesystemAdapter::class),
            [
                Config::OPTION_VISIBILITY => Visibility::PUBLIC,
                Config::OPTION_DIRECTORY_VISIBILITY => Visibility::PUBLIC,
            ],
        ]);

    $services->alias(PublicFilesystemOperator::class, PublicFilesystem::class);

    $services->set(Filesystem::class)
        ->args([service(FilesystemAdapter::class)]);

    $services->alias(FilesystemOperator::class, Filesystem::class);
};
