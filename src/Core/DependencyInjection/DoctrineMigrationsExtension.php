<?php

namespace App\Core\DependencyInjection;

use Doctrine\DBAL\Connection;
use Doctrine\Migrations\Configuration\Configuration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DoctrineMigrationsExtension extends ConfigurableExtension
{
    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('doctrine_migrations.yaml');

        $container->setDefinition(Configuration::class, $this->createMigrationsConfiguration($mergedConfig));
    }

    /**
     * @param array $config
     *
     * @return Definition
     */
    private function createMigrationsConfiguration(array $config): Definition
    {
        $definition = new Definition(Configuration::class);
        $definition->setArguments([new Reference(Connection::class)])
            ->addMethodCall('setName', [$config['name']])
            ->addMethodCall('setMigrationsTableName', [$config['table_name']])
            ->addMethodCall('setMigrationsColumnName', [$config['column_name']])
            ->addMethodCall('setMigrationsColumnLength', [$config['column_length']])
            ->addMethodCall('setMigrationsExecutedAtColumnName', [$config['executed_at_column_name']])
            ->addMethodCall('setMigrationsDirectory', [$config['dir']])
            ->addMethodCall('setMigrationsNamespace', [$config['namespace']])
            ->addMethodCall('setCustomTemplate', [$config['custom_template']])
            ->addMethodCall('setAllOrNothing', [$config['all_or_nothing']]);

        switch ($config['organized_by']) {
            case 'year':
                $definition->addMethodCall('setMigrationsAreOrganizedByYear', [true]);

                break;

            case 'year_and_month':
                $definition->addMethodCall('setMigrationsAreOrganizedByYearAndMonth', [true]);

                break;
        }

        return $definition;
    }
}
