<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('doctrine', [
        'database' => [
            'driver' => 'pdo_pgsql',
            'url' => param('env(resolve:DATABASE_URL)'),
            'server_version' => '13.2',
            'charset' => 'utf8',
        ],
    ]);
};
