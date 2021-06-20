<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container): void {
    $container->extension('assets', [
        'json_manifest_path' => param('kernel.project_dir').'/public/build/app/manifest.json',
        'packages' => [
            'static' => null,
            'api_doc' => [
                'base_path' => '/build/api_doc',
            ],
        ],
    ]);
};
