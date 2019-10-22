<?php

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class DoctrineMigrationsConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('doctrine_migrations');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->scalarNode('name')
                    ->cannotBeEmpty()
                    ->defaultValue('Application migrations')
                ->end()

                ->scalarNode('table_name')
                    ->cannotBeEmpty()
                    ->defaultValue('migration_versions')
                ->end()

                ->scalarNode('column_name')
                    ->cannotBeEmpty()
                    ->defaultValue('version')
                ->end()

                ->integerNode('column_length')
                    ->min(1)
                    ->defaultValue(14)
                ->end()

                ->scalarNode('executed_at_column_name')
                    ->cannotBeEmpty()
                    ->defaultValue('executed_at')
                ->end()

                ->scalarNode('dir')
                    ->cannotBeEmpty()
                    ->defaultValue('%kernel.root_dir%/src/Migrations')
                ->end()

                ->scalarNode('namespace')
                    ->cannotBeEmpty()
                    ->defaultValue('AppMigrations')
                ->end()

                ->enumNode('organized_by')
                    ->values([false, 'year', 'year_and_month'])
                    ->defaultValue(false)
                ->end()

                ->scalarNode('custom_template')
                    ->defaultValue(null)
                ->end()

                ->booleanNode('all_or_nothing')
                    ->defaultValue(false)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
