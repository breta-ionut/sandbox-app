<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine;

use Doctrine\ORM\Tools\Console\Command\RunDqlCommand as BaseRunDqlCommand;

class RunDqlCommand extends BaseRunDqlCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:query:dql';
}
