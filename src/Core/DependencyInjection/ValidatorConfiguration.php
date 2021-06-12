<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ValidatorConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('validator');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->enumNode('email_validation_mode')
                    ->values(['loose', 'strict', 'html5'])
                    ->defaultValue('loose')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
