<?php

namespace App\Core\Command\Doctrine\Migrations;

use App\Core\Command\Doctrine\DoctrineCommandTrait;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand as BaseMigrateCommand;

class MigrateCommand extends BaseMigrateCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:migrations:migrate';
}
