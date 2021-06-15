<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Core\Command\Doctrine\ClearMetadataCacheCommand;
use App\Core\Command\Doctrine\ClearQueryCacheCommand;
use App\Core\Command\Doctrine\ClearResultCacheCommand;
use App\Core\Command\Doctrine\CreateDatabaseCommand;
use App\Core\Command\Doctrine\CreateSchemaCommand;
use App\Core\Command\Doctrine\DropDatabaseCommand;
use App\Core\Command\Doctrine\DropSchemaCommand;
use App\Core\Command\Doctrine\EnsureProductionSettingsCommand;
use App\Core\Command\Doctrine\InfoCommand;
use App\Core\Command\Doctrine\MappingDescribeCommand;
use App\Core\Command\Doctrine\RunDqlCommand;
use App\Core\Command\Doctrine\RunSqlCommand;
use App\Core\Command\Doctrine\UpdateSchemaCommand;
use App\Core\Command\Doctrine\ValidateSchemaCommand;
use App\Core\Doctrine\EventManager;
use App\Core\Doctrine\RepositoryFactory;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('doctrine.orm.proxy_dir', param('kernel.cache_dir').'/doctrine/Proxies');

    $services = $container->services();

    $services->set(EventManager::class);

    $services->set(RepositoryFactory::class);

    $services->set(EntityManager::class)
        ->factory([EntityManager::class, 'create'])
        ->args([abstract_arg('connection'), abstract_arg('config'), service(EventManager::class)]);

    $services->alias(ObjectManager::class, EntityManager::class)
        ->public();

    $services->alias(EntityManagerInterface::class, EntityManager::class)
        ->public();

    $services->set(Connection::class)
        ->factory([service(EntityManager::class), 'getConnection']);

    // Commands.
    $services->set(ClearMetadataCacheCommand::class)
        ->tag('console.command');

    $services->set(ClearQueryCacheCommand::class)
        ->tag('console.command');

    $services->set(ClearResultCacheCommand::class)
        ->tag('console.command');

    $services->set(CreateDatabaseCommand::class)
        ->args([service(Connection::class)])
        ->tag('console.command');

    $services->set(CreateSchemaCommand::class)
        ->tag('console.command');

    $services->set(DropDatabaseCommand::class)
        ->args([service(Connection::class)])
        ->tag('console.command');

    $services->set(DropSchemaCommand::class)
        ->tag('console.command');

    $services->set(EnsureProductionSettingsCommand::class)
        ->tag('console.command');

    $services->set(InfoCommand::class)
        ->tag('console.command');

    $services->set(MappingDescribeCommand::class)
        ->tag('console.command');

    $services->set(RunDqlCommand::class)
        ->tag('console.command');

    $services->set(RunSqlCommand::class)
        ->tag('console.command');

    $services->set(UpdateSchemaCommand::class)
        ->tag('console.command');

    $services->set(ValidateSchemaCommand::class)
        ->tag('console.command');
    // End of - Commands.
};
