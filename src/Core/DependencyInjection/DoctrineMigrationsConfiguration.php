<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Doctrine\Migrations\Configuration\Configuration;
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
            ->fixXmlConfig('migrations_path')
            ->fixXmlConfig('migration')

            ->children()
                ->arrayNode('migrations_paths')
                    ->useAttributeAsKey('namespace')
                    ->normalizeKeys(false)
                    ->defaultValue(['AppMigrations' => '%kernel.project_dir%/src/Migrations'])

                    ->scalarPrototype()->end()
                ->end()

                ->arrayNode('migrations')
                    ->scalarPrototype()
                        ->cannotBeEmpty()
                    ->end()
                ->end()

                ->enumNode('organize_migrations')
                    ->values([
                        Configuration::VERSIONS_ORGANIZATION_NONE,
                        Configuration::VERSIONS_ORGANIZATION_BY_YEAR,
                        Configuration::VERSIONS_ORGANIZATION_BY_YEAR_AND_MONTH,
                    ])
                    ->defaultValue(Configuration::VERSIONS_ORGANIZATION_NONE)
                    ->treatFalseLike(Configuration::VERSIONS_ORGANIZATION_NONE)
                ->end()

                ->scalarNode('custom_template')
                    ->defaultNull()
                ->end()

                ->booleanNode('all_or_nothing')
                    ->defaultFalse()
                ->end()

                ->booleanNode('check_database_platform')
                    ->defaultTrue()
                ->end()

                ->arrayNode('storage')
                    ->addDefaultsIfNotSet()

                    ->children()
                        ->scalarNode('table_name')
                            ->cannotBeEmpty()
                            ->defaultValue('migration_versions')
                        ->end()

                        ->scalarNode('version_column_name')
                            ->cannotBeEmpty()
                            ->defaultValue('version')
                        ->end()

                        ->integerNode('version_column_length')
                            ->min(1)
                            ->defaultValue(191)
                        ->end()

                        ->scalarNode('executed_at_column_name')
                            ->cannotBeEmpty()
                            ->defaultValue('executed_at')
                        ->end()

                        ->scalarNode('execution_time_column_name')
                            ->cannotBeEmpty()
                            ->defaultValue('execution_time')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
