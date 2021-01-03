<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine;

use Doctrine\ORM\Tools\Console\Command\ValidateSchemaCommand as BaseValidateSchemaCommand;

class ValidateSchemaCommand extends BaseValidateSchemaCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:schema:validate';
}
