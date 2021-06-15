<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\EventListener\ErrorListener;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(ErrorListener::class)
        ->args([service(LoggerInterface::class)])
        ->tag('kernel.event_subscriber');
};
