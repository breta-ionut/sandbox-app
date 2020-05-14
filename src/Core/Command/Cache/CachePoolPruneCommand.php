<?php

declare(strict_types=1);

namespace App\Core\Command\Cache;

use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CachePoolPruneCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'cache:pool:prune';

    /**
     * @var PruneableInterface[]|iterable
     */
    private iterable $pools;

    /**
     * @param PruneableInterface[]|iterable $pools
     */
    public function __construct(iterable $pools)
    {
        parent::__construct();

        $this->pools = $pools;
    }

    protected function configure()
    {
        $this->setDescription('Prunes all the cache pools supporting this operation.')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command deletes the expired items (prunes) from all the pruneable cache pools.

    %command.full_name%
EOT
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        $prunedAllPools = true;
        foreach ($this->pools as $name => $pool) {
            $style->comment(\sprintf('Pruning cache pool <info>%s</info>.', $name));

            if (!$pool->prune()) {
                $prunedAllPools = false;

                $style->error(\sprintf('Failed to prune cache pool "%s".', $name));
            }
        }

        if ($prunedAllPools) {
            $style->success('Successfully pruned all cache pools.');
        } else {
            $style->warning('Some cache pools were not pruned.');
        }

        return 0;
    }
}
