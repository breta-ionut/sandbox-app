<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine;

use Doctrine\DBAL\Tools\Console\Command\RunSqlCommand as BaseRunSqlCommand;

class RunSqlCommand extends BaseRunSqlCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:query:sql';
}
