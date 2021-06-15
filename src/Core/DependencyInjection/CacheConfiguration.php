<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class CacheConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('cache');
        $root = $treeBuilder->getRootNode();

        $root
            ->fixXmlConfig('app_adapter')
            ->fixXmlConfig('pool')

            ->children()
                ->scalarNode('prefix_seed')
                    ->info('Used to generate namespaces for the cache keys.')
                ->end()

                ->scalarNode('directory')
                    ->info('The directory where the filesystem cache will be stored.')

                    ->defaultValue('%kernel.cache_dir%/pools')
                ->end()

                ->scalarNode('default_redis_provider')
                    ->info('The DSN of a Redis connection or the id of a Redis client.')
                ->end()

                ->append($this->createAdaptersNode(
                    'app_adapters',
                    'If not specified, the "app" cache pool will rely on the filesystem adapter.'
                ))

                ->arrayNode('pools')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)

                    ->arrayPrototype()
                        ->fixXmlConfig('adapter')

                        ->children()
                            ->append($this->createAdaptersNode(
                                'adapters',
                                'The "app" pool is used if no adapters are specified.'
                            ))

                            ->scalarNode('tags')
                                ->info('Enables tagging when true. Also accepts the tags cache pool id as a value.')

                                ->cannotBeEmpty()
                            ->end()

                            ->booleanNode('public')
                                ->defaultFalse()
                            ->end()

                            ->scalarNode('default_lifetime')->end()

                            ->scalarNode('clearer')
                                ->cannotBeEmpty()
                            ->end()
                        ->end()

                        ->validate()
                            ->ifTrue(fn(array $value): bool => isset($value['app']) || isset($value['system']))
                            ->thenInvalid('"app" and "system" are reserved pool names.')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    private function createAdaptersNode(string $name, string $info): ArrayNodeDefinition
    {
        return (new ArrayNodeDefinition($name))
            ->info($info.' Multiple adapters are chained into a single one via a ChainAdapter.')

            ->requiresAtLeastOneElement()
            ->performNoDeepMerging()

            ->beforeNormalization()
                ->ifString()
                ->then(static fn(string $value): array => [['id' => $value]])
            ->end()

            ->arrayPrototype()
                ->beforeNormalization()
                    ->ifString()
                    ->then(static fn(string $value): array => ['id' => $value])
                ->end()

                ->children()
                    ->scalarNode('id')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()

                    ->scalarNode('provider')
                        ->info('A provider DSN or id to replace the adapter\'s default.')
                    ->end()
                ->end()
            ->end();
    }
}
