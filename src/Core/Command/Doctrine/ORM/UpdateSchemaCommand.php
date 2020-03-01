<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine\ORM;

use App\Core\Command\Doctrine\DoctrineCommandTrait;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand as BaseUpdateSchemaCommand;

class UpdateSchemaCommand extends BaseUpdateSchemaCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:schema:update';
}
