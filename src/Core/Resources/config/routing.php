<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Core\Routing\AnnotationControllerLoader;
use App\Core\Routing\RedirectableCompiledUrlMatcher;
use App\Core\Routing\RootLoader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Routing\Loader\AnnotationFileLoader;
use Symfony\Component\Routing\Loader\ContainerLoader;
use Symfony\Component\Routing\Loader\GlobFileLoader;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Loaders.
    $services->set(RootLoader::class)
        ->args([service(PhpFileLoader::class), param('kernel.config_dir'), param('kernel.environment')])
        ->public();

    $services->set('routing.file_locator', FileLocator::class);

    $services->set(AnnotationControllerLoader::class)
        ->args([null, param('kernel.environment')]);

    $services->set('routing.resolver', LoaderResolver::class);

    $services->set(AnnotationDirectoryLoader::class)
        ->args([service('routing.file_locator'), service(AnnotationControllerLoader::class)])
        ->tag('routing.loader');

    $services->set(AnnotationFileLoader::class)
        ->args([service('routing.file_locator'), service(AnnotationControllerLoader::class)])
        ->tag('routing.loader');

    $services->set(ContainerLoader::class)
        ->args([service('service_container'), param('kernel.environment')])
        ->tag('routing.loader');

    $services->set(GlobFileLoader::class)
        ->args([service('routing.file_locator'), param('kernel.environment')])
        ->tag('routing.loader');

    $services->set(PhpFileLoader::class)
        ->args([service('routing.file_locator'), param('kernel.environment')])
        ->tag('routing.loader');

    $services->set(YamlFileLoader::class)
        ->args([service('routing.file_locator'), param('kernel.environment')])
        ->tag('routing.loader');

    $services->set('routing.loader', DelegatingLoader::class)
        ->args([service('routing.resolver')]);
    // End of - Loaders.

    $services->set(RequestContext::class);

    $services->set(Router::class)
        ->args([
            service('routing.loader'),
            \sprintf('%s::load', RootLoader::class),
            [
                'cache_dir' => param('kernel.cache_dir'),
                'debug' => param('kernel.debug'),
                'matcher_class' => RedirectableCompiledUrlMatcher::class,
                'resource_type' => 'service',
            ],
            service(RequestContext::class),
        ]);

    $services->alias(RouterInterface::class, Router::class);
    $services->alias(UrlGeneratorInterface::class, Router::class);
    $services->alias(RequestMatcherInterface::class, Router::class);
    $services->alias(UrlMatcherInterface::class, Router::class);

    $services->set(RouterListener::class)
        ->args([
            service(RequestMatcherInterface::class),
            service(RequestStack::class),
            service(RequestContext::class),
            service(LoggerInterface::class),
            param('kernel.project_dir'),
            param('kernel.debug'),
        ])
        ->tag('kernel.event_subscriber');
};
