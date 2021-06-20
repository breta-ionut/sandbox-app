<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\Frontend\\', '../../src/Frontend/*');

    $services->load('App\\Frontend\\Controller\\', '../../src/Frontend/Controller')
        ->tag('controller.service_arguments');
};
