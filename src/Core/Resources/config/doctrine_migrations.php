<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ExistingConfiguration;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\CurrentCommand;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\ListCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\SyncMetadataCommand;
use Doctrine\Migrations\Tools\Console\Command\UpToDateCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ExistingConfiguration::class)
        ->args([abstract_arg('configurations')]);

    $services->set(ExistingEntityManager::class)
        ->args([service(EntityManagerInterface::class)]);

    $services->set(DependencyFactory::class)
        ->factory([DependencyFactory::class, 'fromEntityManager'])
        ->args([
            service(ExistingConfiguration::class),
            service(ExistingEntityManager::class),
            service(LoggerInterface::class),
        ]);

    // Commands.
    $services->set(CurrentCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:current'])
        ->tag('console.command', ['command' => 'doctrine:migrations:current']);

    $services->set(DiffCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:diff'])
        ->tag('console.command', ['command' => 'doctrine:migrations:diff']);

    $services->set(DumpSchemaCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:dump-schema'])
        ->tag('console.command', ['command' => 'doctrine:migrations:dump-schema']);

    $services->set(ExecuteCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:execute'])
        ->tag('console.command', ['command' => 'doctrine:migrations:execute']);

    $services->set(GenerateCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:generate'])
        ->tag('console.command', ['command' => 'doctrine:migrations:generate']);

    $services->set(LatestCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:latest'])
        ->tag('console.command', ['command' => 'doctrine:migrations:latest']);

    $services->set(ListCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:list'])
        ->tag('console.command', ['command' => 'doctrine:migrations:list']);

    $services->set(MigrateCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:migrate'])
        ->tag('console.command', ['command' => 'doctrine:migrations:migrate']);

    $services->set(RollupCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:rollup'])
        ->tag('console.command', ['command' => 'doctrine:migrations:rollup']);

    $services->set(StatusCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:status'])
        ->tag('console.command', ['command' => 'doctrine:migrations:status']);

    $services->set(SyncMetadataCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:sync-metadata-storage'])
        ->tag('console.command', ['command' => 'doctrine:migrations:sync-metadata-storage']);

    $services->set(UpToDateCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:up-to-date'])
        ->tag('console.command', ['command' => 'doctrine:migrations:up-to-date']);

    $services->set(VersionCommand::class)
        ->args([service(DependencyFactory::class), 'doctrine:migrations:version'])
        ->tag('console.command', ['command' => 'doctrine:migrations:version']);
    // End of - Commands.
};
