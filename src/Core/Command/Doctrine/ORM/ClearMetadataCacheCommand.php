<?php

namespace App\Core\Command\Doctrine\ORM;

use App\Core\Command\Doctrine\DoctrineCommandTrait;
use Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand as BaseClearMetadataCacheCommand;

class ClearMetadataCacheCommand extends BaseClearMetadataCacheCommand
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:cache:clear-metadata';
}
