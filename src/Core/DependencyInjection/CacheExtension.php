<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class CacheExtension extends ConfigurableExtension
{
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('cache.yaml');


    }

    /**
     * @param ContainerBuilder $container
     * @param array            $poolsConfig
     */
    private function createPools(ContainerBuilder $container, array $poolsConfig): void
    {
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
    }
}
