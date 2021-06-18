<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Debug\FileLinkFormatter;
use Symfony\Component\HttpKernel\EventListener\DebugHandlersListener;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('php_errors.throw_at', -1);

    $services = $container->services();

    $services->set(FileLinkFormatter::class);

    $services->set(DebugHandlersListener::class)
        ->args([
            null,
            service(LoggerInterface::class),
            null,
            param('php_errors.throw_at'),
            param('kernel.debug'),
            service(FileLinkFormatter::class),
            param('kernel.debug'),
            service(LoggerInterface::class),
        ])
        ->tag('kernel.event_subscriber');
};
