<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Core\Command\Cache\CacheClearCommand;
use App\Core\Command\Cache\CachePoolClearCommand;
use App\Core\Command\Cache\CachePoolDeleteCommand;
use App\Core\Command\Cache\CachePoolPruneCommand;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\Marshaller\DefaultMarshaller;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\ChainCacheClearer;
use Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(DefaultMarshaller::class);

    // Adapters.
    $services->set('cache.adapter.apcu', ApcuAdapter::class)
        ->abstract()
        ->args([abstract_arg('namespace'), 0, abstract_arg('version')])
        ->call('setLogger', [service(LoggerInterface::class)])
        ->tag('cache.pool', ['clearer' => 'cache.clearer.default', 'reset' => 'reset']);

    $services->set('cache.adapter.array', ArrayAdapter::class)
        ->abstract()
        ->call('setLogger', [service(LoggerInterface::class)])
        ->tag('cache.pool', ['clearer' => 'cache.clearer.default', 'reset' => 'reset']);

    $services->set('cache.adapter.filesystem', FilesystemAdapter::class)
        ->abstract()
        ->args([abstract_arg('namespace'), 0, abstract_arg('directory'), service(DefaultMarshaller::class)])
        ->call('setLogger', [service(LoggerInterface::class)])
        ->tag('cache.pool', ['clearer' => 'cache.clearer.default', 'reset' => 'reset']);

    $services->set('cache.adapter.redis', RedisAdapter::class)
        ->abstract()
        ->args([abstract_arg('provider'), abstract_arg('namespace'), 0, service(DefaultMarshaller::class)])
        ->call('setLogger', [service(LoggerInterface::class)])
        ->tag('cache.pool', [
            'provider' => 'cache.default_provider.redis',
            'clearer' => 'cache.clearer.default',
            'reset' => 'reset',
        ]);

    $services->set('cache.adapter.system', AdapterInterface::class)
        ->abstract()
        ->factory([AbstractAdapter::class, 'createSystemCache'])
        ->args([
            abstract_arg('namespace'),
            0,
            abstract_arg('version'),
            abstract_arg('directory'),
            service(LoggerInterface::class),
        ])
        ->tag('cache.pool', ['clearer' => 'cache.clearer.system', 'reset' => 'reset']);
    // End of - Adapters.

    $services->set('cache.app');
    $services->alias(CacheItemPoolInterface::class, 'cache.app');
    $services->alias(CacheInterface::class, 'cache.app');

    $services->set('cache.app.taggable', TagAwareAdapter::class)
        ->args([service('cache.app')])
        ->public();

    $services->alias(TagAwareCacheInterface::class, 'cache.app.taggable');

    // System pools.
    $services->set('cache.system')
        ->parent('cache.adapter.system')
        ->public()
        ->tag('cache.pool');

    $services->set('cache.property_access', AdapterInterface::class)
        ->factory([PropertyAccessor::class, 'createCache'])
        ->args([abstract_arg('namespace'), 0, abstract_arg('version'), service(LoggerInterface::class)])
        ->tag('cache.pool', ['clearer' => 'cache.clearer.system']);

    $services->set('cache.property_info')
        ->parent('cache.adapter.system')
        ->tag('cache.pool');

    $services->set('cache.serializer')
        ->parent('cache.adapter.system')
        ->tag('cache.pool');

    $services->set('cache.validator')
        ->parent('cache.adapter.system')
        ->tag('cache.pool');
    // End of - System pools.

    // Clearers.
    $services->set('cache.clearer.default', Psr6CacheClearer::class)
        ->args([abstract_arg('pools')]);

    $services->set('cache.clearer.global')
        ->parent('cache.clearer.default');

    $services->set('cache.clearer.system')
        ->parent('cache.clearer.default');
    // End of - Clearers.

    // Commands.
    $services->set(CacheClearCommand::class)
        ->args([param('kernel.cache_dir'), service(Filesystem::class), service(ChainCacheClearer::class)])
        ->tag('console.command');

    $services->set(CachePoolClearCommand::class)
        ->args([service('cache.clearer.global')])
        ->tag('console.command');

    $services->set(CachePoolDeleteCommand::class)
        ->args([service('cache.clearer.global')])
        ->tag('console.command');

    $services->set(CachePoolPruneCommand::class)
        ->args([abstract_arg('pools')])
        ->tag('console.command');
    // End of - Commands.
};
