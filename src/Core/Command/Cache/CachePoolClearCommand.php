<?php

declare(strict_types=1);

namespace App\Core\Command\Cache;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer;

class CachePoolClearCommand extends Command
{
    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'cache:pool:clear';

    public function __construct(private Psr6CacheClearer $cacheClearer)
    {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Clears cache pools.')
            ->addArgument('pools', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'The cache pools to clear.')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command clears a given set of cache pools.

    %command.full_name% <cache pool 1> [...<cache pool N>]
EOT
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        foreach ($input->getArgument('pools') as $pool) {
            if (!$this->cacheClearer->hasPool($pool)) {
                throw new \InvalidArgumentException(\sprintf('No cache pool "%s" found.', $pool));
            }

            $style->comment(\sprintf('Clearing cache pool <info>%s</info>.', $pool));

            if (!$this->cacheClearer->clearPool($pool)) {
                throw new \RuntimeException(\sprintf('Failed to clear cache pool "%s".', $pool));
            }
        }

        $style->success('Successfully cleared cache pool(s).');

        return self::SUCCESS;
    }
}
