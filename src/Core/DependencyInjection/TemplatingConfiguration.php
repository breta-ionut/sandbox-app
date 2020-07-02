<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class TemplatingConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('templating');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->scalarNode('templates_dir')
                    ->defaultValue('%kernel.project_dir%/templates')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
