<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Core\Templating\EngineFactory;
use App\Core\Templating\Helper\AssetsHelper;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Templating\Helper\SlotsHelper;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\TemplateNameParserInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(TemplateNameParser::class);
    $services->alias(TemplateNameParserInterface::class, TemplateNameParser::class);

    $services->set(FilesystemLoader::class)
        ->args([abstract_arg('template path patterns')]);

    $services->alias(LoaderInterface::class, FilesystemLoader::class);

    // Helpers.
    $services->set(AssetsHelper::class)
        ->args([service(Packages::class)])
        ->tag('templating.helper');

    $services->set(SlotsHelper::class)
        ->tag('templating.helper');
    // End of - Helpers.

    $services->set(EngineInterface::class)
        ->factory([EngineFactory::class, 'create'])
        ->args([
            service(TemplateNameParserInterface::class),
            service(LoaderInterface::class),
            tagged_iterator('templating.helper'),
        ]);
};
