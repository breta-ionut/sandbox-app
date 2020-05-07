<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class CacheConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('cache');
        $root = $treeBuilder->getRootNode();

        $root
            ->fixXmlConfig('default_provider')
            ->fixXmlConfig('pool')

            ->children()
                ->scalarNode('prefix_seed')
                    ->info('Used to generate namespaces for the cache keys.')
                ->end()

                ->scalarNode('directory')
                    ->info('The directory where the filesystem cache will be stored.')

                    ->defaultValue('%kernel.cache_dir%/pools')
                ->end()

                ->arrayNode('default_providers')
                    ->addDefaultsIfNotSet()

                    ->children()
                        ->scalarNode('doctrine')
                            ->info('The id of a Doctrine cache provider.')

                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('redis')
                            ->info('The DSN of a Redis connection or the id of a Redis client.')
                        ->end()
                    ->end()
                ->end()

                ->scalarNode('app_adapter')
                    ->info('The id of the "app" cache pool adapter. If not specified, the filesystem adapter is used.')

                    ->cannotBeEmpty()
                ->end()

                ->arrayNode('pools')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)

                    ->arrayPrototype()
                        ->fixXmlConfig('adapter')

                        ->children()
                            ->arrayNode('adapters')
                                ->info(
                                    'The "app" pool is used if no adapters are specified. '
                                    .'Multiple adapters are chained into a single one via a ChainAdapter.'
                                )

                                ->requiresAtLeastOneElement()

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
                                ->end()
                            ->end()

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
}
