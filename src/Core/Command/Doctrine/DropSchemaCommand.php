<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine;

use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand as BaseDropSchemaCommand;

class DropSchemaCommand extends BaseDropSchemaCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:schema:drop';
}
