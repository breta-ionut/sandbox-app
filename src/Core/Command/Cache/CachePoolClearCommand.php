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
    private const RETURN_CODE_SUCCESS = 0;
    private const RETURN_CODE_ERROR = 1;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'cache:pool:clear';

    private Psr6CacheClearer $cacheClearer;

    /**
     * @param Psr6CacheClearer $cacheClearer
     */
    public function __construct(Psr6CacheClearer $cacheClearer)
    {
        parent::__construct(null);

        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        foreach ($input->getArgument('pools') as $pool) {
            if (!$this->cacheClearer->hasPool($pool)) {
                $style->error(\sprintf('No "%s" cache pool found.', $pool));

                return self::RETURN_CODE_ERROR;
            }

            $style->comment(\sprintf('Clearing cache pool <info>%s</info>.', $pool));

            $this->cacheClearer->clearPool($pool);
        }

        $style->success('Successfully cleared cache pool(s).');

        return self::RETURN_CODE_SUCCESS;
    }
}
