<?php

declare(strict_types=1);

namespace App\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class CacheClearCommand extends Command
{
    private const RETURN_CODE_SUCCESS = 0;
    private const RETURN_CODE_ERROR = 1;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'cache:clear';

    private string $cacheDir;
    private Filesystem $filesystem;
    private CacheClearerInterface $cacheClearer;

    /**
     * @param string                $cacheDir
     * @param Filesystem            $filesystem
     * @param CacheClearerInterface $cacheClearer
     */
    public function __construct(string $cacheDir, Filesystem $filesystem, CacheClearerInterface $cacheClearer)
    {
        parent::__construct();

        $this->cacheDir = $cacheDir;
        $this->filesystem = $filesystem;
        $this->cacheClearer = $cacheClearer;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setDescription('Clears the application cache.')
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> clears the application cache for a given environment and debug mode:

    <info>%command.full_name% --env=dev</info>
    <info>%command.full_name% --env=prod --no-debug</info>
EOT
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        if (!\is_writable($this->cacheDir)) {
            $style->error(\sprintf('Cache directory "%s" is not writable.', $this->cacheDir));

            return self::RETURN_CODE_ERROR;
        }

        /** @var KernelInterface $kernel */
        $kernel = $this->getApplication()->getKernel();

        $style->comment(\sprintf(
            'Clearing cache for the <info>%s</info> environment with debug mode <info>%s</info>.',
            $kernel->getEnvironment(),
            \var_export($kernel->isDebug(), true)
        ));

        $oldCacheDir = \substr($this->cacheDir, 0, -1).('~' === \substr($this->cacheDir, -1) ? '+' : '~');
        $this->filesystem->remove($oldCacheDir);

        $this->cacheClearer->clear($this->cacheDir);

        $this->filesystem->rename($this->cacheDir, $oldCacheDir);

        try {
            $this->filesystem->remove($oldCacheDir);
        } catch (IOException $exception) {
            $style->warning(\sprintf(
                'Could not remove old cache directory "%s": %s',
                $oldCacheDir,
                $exception->getMessage()
            ));
        }

        $style->success(\sprintf(
            'Successfully cleared cache for the "%s" environment with debug mode "%s".',
            $kernel->getEnvironment(),
            \var_export($kernel->isDebug(), true)
        ));

        return self::RETURN_CODE_SUCCESS;
    }
}
