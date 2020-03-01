<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine\ORM;

use App\Core\Command\Doctrine\DoctrineCommandTrait;
use Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand as BaseClearResultCacheCommand;

class ClearResultCacheCommand extends BaseClearResultCacheCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:cache:clear-result';
}
