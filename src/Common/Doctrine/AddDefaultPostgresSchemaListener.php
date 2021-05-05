<?php

declare(strict_types=1);

namespace App\Common\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Schema\PostgreSqlSchemaManager;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;

class AddDefaultPostgresSchemaListener implements EventSubscriber
{
    /**
     * @param GenerateSchemaEventArgs $eventArgs
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $eventArgs): void
    {
        $schemaManager = $eventArgs->getEntityManager()
            ->getConnection()
            ->getSchemaManager();

        if (!$schemaManager instanceof PostgreSqlSchemaManager) {
            return;
        }

        $schema = $eventArgs->getSchema();

        foreach ($schemaManager->getExistingSchemaSearchPaths() as $namespace) {
            if (!$schema->hasNamespace($namespace)) {
                $schema->createNamespace($namespace);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents(): array
    {
        return [ToolEvents::postGenerateSchema];
    }
}
