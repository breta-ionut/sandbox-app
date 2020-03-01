<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine\Migrations;

use App\Core\Command\Doctrine\DoctrineCommandTrait;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand as BaseRollupCommand;

class RollupCommand extends BaseRollupCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:migrations:rollup';
}
