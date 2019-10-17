<?php

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DoctrineConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('doctrine');
        $root = $treeBuilder->getRootNode();

        $root
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

                ->arrayNode('default_table_options')
                    ->children()
                        ->scalarNode('charset')
                            ->cannotBeEmpty()
                        ->end()

                        ->scalarNode('collate')
                            ->cannotBeEmpty()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
