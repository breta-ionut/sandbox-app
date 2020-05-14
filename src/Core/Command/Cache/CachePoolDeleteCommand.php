<?php

declare(strict_types=1);

namespace App\Core\Command\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer;

class CachePoolDeleteCommand extends Command
{
    private const RETURN_CODE_SUCCESS = 0;
    private const RETURN_CODE_ERROR = 1;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'cache:pool:delete';

    private Psr6CacheClearer $cacheClearer;

    /**
     * @param Psr6CacheClearer $cacheClearer
     */
    public function __construct(Psr6CacheClearer $cacheClearer)
    {
        parent::__construct();

        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setDescription('Deletes a specific item from a cache pool.')
            ->addArgument('pool', InputArgument::REQUIRED, 'The pool to delete from.')
            ->addArgument('key', InputArgument::REQUIRED, 'The item to delete.')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command deletes a given item from a cache pool.

    %command.full_name% <pool> <key>
EOT
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        $poolName = $input->getArgument('pool');
        if (!$this->cacheClearer->hasPool($poolName)) {
            $style->error(\sprintf('No cache pool "%s" found.', $poolName));

            return self::RETURN_CODE_ERROR;
        }

        /** @var CacheItemPoolInterface $pool */
        $pool = $this->cacheClearer->getPool($poolName);

        $key = $input->getArgument('key');
        if (!$pool->hasItem($key)) {
            $style->note(\sprintf('No item "%s" found in cache pool "%s".', $key, $poolName));

            return self::RETURN_CODE_SUCCESS;
        }

        if (!$pool->deleteItem($key)) {
            $style->error(\sprintf('Item "%s" from cache pool "%s" could not be deleted.', $key, $poolName));

            return self::RETURN_CODE_ERROR;
        }

        $style->success(\sprintf('Successfully deleted item "%s" from cache pool "%s".', $key, $poolName));

        return self::RETURN_CODE_SUCCESS;
    }
}
