<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Core\Kernel;
use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\CacheClearer\ChainCacheClearer;
use Symfony\Component\HttpKernel\DependencyInjection\ServicesResetter;
use Symfony\Component\HttpKernel\Log\Logger;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('kernel', Kernel::class)
        ->public()
        ->synthetic();

    $services->set('services_resetter', ServicesResetter::class)
        ->args([abstract_arg('resettable services'), abstract_arg('reset methods')])
        ->public();

    $services->set(ContainerBag::class)
        ->args([service('service_container')]);

    $services->alias(ContainerBagInterface::class, ContainerBag::class);
    $services->alias(ParameterBagInterface::class, ContainerBag::class);

    $services->set(EventDispatcher::class);
    $services->alias(EventDispatcherInterface::class, EventDispatcher::class);
    $services->alias(ContractsEventDispatcherInterface::class, EventDispatcher::class);

    $services->alias(PsrEventDispatcherInterface::class, EventDispatcher::class)
        ->public();

    $services->set(ChainCacheClearer::class)
        ->args([tagged_iterator('kernel.cache_clearer')]);

    $services->alias(CacheClearerInterface::class, ChainCacheClearer::class);

    $services->set(Logger::class);
    $services->alias(LoggerInterface::class, Logger::class);

    $services->set(Filesystem::class);
};
