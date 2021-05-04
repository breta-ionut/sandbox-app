<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DoctrineConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('doctrine');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->arrayNode('database')
                    ->children()
                        ->scalarNode('driver')
                            ->isRequired()
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('url')
                            ->isRequired()
                        ->end()

                        ->scalarNode('server_version')->end()

                        ->scalarNode('charset')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()

                ->arrayNode('orm')
                    ->addDefaultsIfNotSet()

                    ->children()
                        ->scalarNode('mapping_dir')
                            ->defaultValue('%kernel.config_dir%/doctrine')
                        ->end()

                        ->scalarNode('namespace_prefix_pattern')
                            ->cannotBeEmpty()
                            ->defaultValue('App\\%s\\Model')
                        ->end()

                        ->scalarNode('metadata_cache_driver')
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('query_cache_driver')
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('result_cache_driver')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
