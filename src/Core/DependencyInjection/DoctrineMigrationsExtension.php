<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\Metadata\Storage\TableMetadataStorageConfiguration;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DoctrineMigrationsExtension extends ConfigurableExtension
{
    use ConfigurationExtensionTrait;

    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('doctrine_migrations.php');

        $container->getDefinition(ExistingConfiguration::class)
            ->setArgument('$configurations', $this->createMigrationsConfiguration($mergedConfig));
    }

    private function createMigrationsConfiguration(array $config): Definition
    {
        $definition = (new Definition(Configuration::class))
            ->addMethodCall('setMigrationOrganization', [$config['organize_migrations']])
            ->addMethodCall('setCustomTemplate', [$config['custom_template']])
            ->addMethodCall('setAllOrNothing', [$config['all_or_nothing']])
            ->addMethodCall('setCheckDatabasePlatform', [$config['check_database_platform']])
            ->addMethodCall(
                'setMetadataStorageConfiguration',
                [$this->createMigrationsStorageConfiguration($config['storage'])]
            );

        foreach ($config['migrations_paths'] as $namespace => $path) {
            $definition->addMethodCall('addMigrationsDirectory', [$namespace, $path]);
        }
        foreach ($config['migrations'] as $migration) {
            $definition->addMethodCall('addMigrationClass', [$migration]);
        }

        $definition->addMethodCall('freeze');

        return $definition;
    }

    private function createMigrationsStorageConfiguration(array $config): Definition
    {
        return (new Definition(TableMetadataStorageConfiguration::class))
            ->addMethodCall('setTableName', [$config['table_name']])
            ->addMethodCall('setVersionColumnName', [$config['version_column_name']])
            ->addMethodCall('setVersionColumnLength', [$config['version_column_length']])
            ->addMethodCall('setExecutedAtColumnName', [$config['executed_at_column_name']])
            ->addMethodCall('setExecutionTimeColumnName', [$config['execution_time_column_name']]);
    }
}
