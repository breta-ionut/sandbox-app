<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine\Migrations;

use App\Core\Command\Doctrine\DoctrineCommandTrait;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand as BaseVersionCommand;

class VersionCommand extends BaseVersionCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:migrations:version';
}
