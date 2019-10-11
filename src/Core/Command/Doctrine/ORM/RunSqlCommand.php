<?php

namespace App\Core\Command\Doctrine\ORM;

use App\Core\Command\Doctrine\DoctrineCommandTrait;
use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand as BaseRunSqlCommand;

class RunSqlCommand extends BaseRunSqlCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:query:sql';
}
