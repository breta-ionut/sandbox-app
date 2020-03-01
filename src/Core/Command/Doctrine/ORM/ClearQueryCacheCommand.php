<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine\ORM;

use App\Core\Command\Doctrine\DoctrineCommandTrait;
use Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand as BaseClearQueryCacheCommand;

class ClearQueryCacheCommand extends BaseClearQueryCacheCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:cache:clear-query';
}
