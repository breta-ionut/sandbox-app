<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class SerializerConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('serializer');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->scalarNode('name_converter')
                    ->cannotBeEmpty()
                ->end()

                ->scalarNode('circular_reference_handler')
                    ->cannotBeEmpty()
                ->end()

                ->scalarNode('max_depth_handler')
                    ->cannotBeEmpty()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
