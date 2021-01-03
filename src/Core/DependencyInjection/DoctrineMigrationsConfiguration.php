<?php

declare(strict_types=1);

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
            ->fixXmlConfig('migration_path')
            ->fixXmlConfig('migration')

            ->children()
                ->arrayNode('migration_paths')
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
            ->end();

        return $treeBuilder;
    }
}
