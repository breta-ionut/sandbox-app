<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine;

use Doctrine\ORM\Tools\Console\Command\InfoCommand as BaseInfoCommand;

class InfoCommand extends BaseInfoCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:mapping:info';
}
