<?php

declare(strict_types=1);

namespace App\Core\Command;

use App\Core\Kernel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
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
            ->addOption('no-warmup', 'N', InputOption::VALUE_NONE, 'If cache should not be warmed.')
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

        $oldCacheDir = $this->clearCache();
        $warmupDir = $this->warmupCache(!$input->getOption('no-warmup'));
        $this->activateNewCache($oldCacheDir, $warmupDir, $style);

        $style->success(\sprintf(
            'Successfully cleared cache for the "%s" environment with debug mode "%s".',
            $kernel->getEnvironment(),
            \var_export($kernel->isDebug(), true)
        ));

        return self::RETURN_CODE_SUCCESS;
    }

    /**
     * @return string The old cache directory.
     */
    private function clearCache(): string
    {
        $oldCacheDir = \substr($this->cacheDir, 0, -1).('~' === \substr($this->cacheDir, -1) ? '+' : '~');
        $this->filesystem->remove($oldCacheDir);

        $this->cacheClearer->clear($this->cacheDir);

        return $oldCacheDir;
    }

    /**
     * @param bool $rebootKernel
     *
     * @return string The new cache directory.
     */
    private function warmupCache(bool $rebootKernel): string
    {
        $warmupDir = \substr($this->cacheDir, 0, -1).('_' === \substr($this->cacheDir, -1) ? '-' : '_');
        $this->filesystem->remove($warmupDir);

        /** @var Kernel $kernel */
        $kernel = $this->getApplication()->getKernel();

        $oldContainerReflection = new \ReflectionObject($kernel->getContainer());
        $oldContainerDir = \basename(\dirname($oldContainerReflection->getFileName()));

        if ($rebootKernel) {
            $kernel->reboot($warmupDir);

            // Fix cached files references to use the real cache directory in the new cache files.
            $search = [$warmupDir, \str_replace('\\', '\\\\', $warmupDir)];
            $replace = \str_replace('\\', '/', $this->cacheDir);

            foreach (Finder::create()->files()->in($warmupDir) as $file) {
                $filepath = $file->getRealPath();

                $content = \str_replace($search, $replace, \file_get_contents($filepath), $count);
                if ($count) {
                    $this->filesystem->dumpFile($filepath, $content);
                }
            }
        }

        // Ensure the old container exists in the new cache as it might still be used by parallel requests.
        $oldContainerInWarmupDirPath = $warmupDir.'/'.$oldContainerDir;
        if (!$this->filesystem->exists($oldContainerInWarmupDirPath)) {
            $this->filesystem->rename($this->cacheDir.'/'.$oldContainerDir, $oldContainerInWarmupDirPath);
            $this->filesystem->touch($oldContainerInWarmupDirPath.'.legacy');
        }

        return $warmupDir;
    }

    /**
     * @param string       $oldCacheDir
     * @param string       $warmupDir
     * @param SymfonyStyle $style
     */
    private function activateNewCache(string $oldCacheDir, string $warmupDir, SymfonyStyle $style): void
    {
        $this->filesystem->rename($this->cacheDir, $oldCacheDir);
        $this->filesystem->rename($warmupDir, $this->cacheDir);

        try {
            $this->filesystem->remove($oldCacheDir);
        } catch (IOException $exception) {
            $style->warning(\sprintf(
                'Could not remove old cache directory "%s": %s',
                $oldCacheDir,
                $exception->getMessage()
            ));
        }
    }
}
