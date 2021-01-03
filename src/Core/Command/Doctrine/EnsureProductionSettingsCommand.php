<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine;

use Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand as BaseEnsureProductionSettingsCommand;

class EnsureProductionSettingsCommand extends BaseEnsureProductionSettingsCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:ensure-production-settings';
}
