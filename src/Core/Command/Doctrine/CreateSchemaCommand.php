<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine;

use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand as BaseCreateSchemaCommand;

class CreateSchemaCommand extends BaseCreateSchemaCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:schema:create';
}
