<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Cache\DependencyInjection\CachePoolPass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class CacheExtension extends ConfigurableExtension
{
    use ConfigurationExtensionTrait;

    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('cache.php');

        if (isset($config['prefix_seed'])) {
            $container->setParameter(
                'cache.prefix_seed',
                $container->resolveEnvPlaceholders($config['prefix_seed'], true)
            );
        }

        $this->configureAdaptersAndDefaultProviders($container, $mergedConfig);
        $this->createAndConfigurePools($container, $mergedConfig);
    }

    private function configureAdaptersAndDefaultProviders(ContainerBuilder $container, array $config): void
    {
        $version = new Parameter('container.build_id');
        foreach (['cache.adapter.apcu', 'cache.adapter.system'] as $id) {
            $container->getDefinition($id)->setArgument('$version', $version);
        }

        $directory = $config['directory'];
        foreach (['cache.adapter.filesystem', 'cache.adapter.system'] as $id) {
            $container->getDefinition($id)->setArgument('$directory', $directory);
        }

        if (isset($config['default_redis_provider'])) {
            $container->setAlias(
                'cache.default_provider.redis',
                new Alias(CachePoolPass::getServiceProvider($container, $container['default_redis_provider']), true)
            );
        }
    }

    private function createAndConfigurePools(ContainerBuilder $container, array $config): void
    {
        $poolsConfig = [
            'app' => [
                'adapters' => $config['app_adapters'] ?: [['id' => 'cache.adapter.filesystem']],
                'public' => true,
            ],
        ] + $config['pools'];

        foreach ($poolsConfig as $name => $poolConfig) {
            $adapters = $poolConfig['adapters']
                ? \array_column($poolConfig['adapters'], 'id', 'provider')
                : ['cache.app'];

            if (1 === \count($adapters)) {
                $definition = new ChildDefinition(\reset($adapters));

                if (!\is_int($provider = \array_key_first($adapters))) {
                    $poolConfig['provider'] = $provider;
                }
            } else {
                $definition = new Definition(ChainAdapter::class, $adapters);
                $poolConfig['reset'] = 'reset';
            }

            $id = 'cache.'.$name;
            $public = $poolConfig['public'];

            if (!empty($poolConfig['tags'])) {
                $taggedId = $id;
                $id .= '.inner';

                $container->register($taggedId, TagAwareAdapter::class)
                    ->setArguments([
                        new Reference($id),
                        \is_string($poolConfig['tags']) ? new Reference($poolConfig['tags']) : null,
                    ])
                    ->setPublic($public);

                $public = false;
            }

            $poolConfig['name'] = $name;
            unset($poolConfig['adapters'], $poolConfig['tags'], $poolConfig['public']);

            $definition->setPublic($public)
                ->addTag('cache.pool', $poolConfig);

            $container->setDefinition($id, $definition);
        }

        if (!$container->getParameter('kernel.debug')) {
            $container->getDefinition('cache.property_access')
                ->setArgument('$version', new Parameter('container.build_id'));
        } else {
            $container->register('cache.property_access', ArrayAdapter::class)->setArguments([0, false]);
        }
    }
}
