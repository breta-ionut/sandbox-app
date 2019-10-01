<?php

namespace App\Core\Console;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class Application extends BaseApplication
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var bool
     */
    private $commandsRegistered = false;

    /**
     * @param KernelInterface $kernel
     * @param string          $name
     * @param string          $version
     */
    public function __construct(KernelInterface $kernel, string $name, string $version)
    {
        parent::__construct($name, $version);

        $this->kernel = $kernel;
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->registerCommands();
        $this->setDispatcher($this->kernel->getContainer()->get(EventDispatcherInterface::class));

        return parent::doRun($input, $output);
    }

    /**
     * {@inheritDoc}
     */
    public function getLongVersion()
    {
        return parent::getLongVersion()
            .sprintf(
                ' (env: <comment>%s</>, debug: <comment>%s</>)',
                $this->kernel->getEnvironment(),
                $this->kernel->isDebug() ? 'true' : 'false'
            );
    }

    /**
     * {@inheritDoc}
     */
    public function add(Command $command)
    {
        $this->registerCommands();

        return parent::add($command);
    }

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        $this->registerCommands();

        return parent::get($name);
    }

    /**
     * {@inheritDoc}
     */
    public function has($name)
    {
        $this->registerCommands();

        return parent::has($name);
    }

    /**
     * {@inheritDoc}
     */
    public function find($name)
    {
        $this->registerCommands();

        return parent::find($name);
    }

    /**
     * {@inheritDoc}
     */
    public function all($namespace = null)
    {
        $this->registerCommands();

        return parent::all($namespace);
    }

    private function registerCommands(): void
    {
        if ($this->commandsRegistered) {
            return;
        }

        $this->commandsRegistered = true;

        $this->kernel->boot();
        $container = $this->kernel->getContainer();

        $this->setCommandLoader($container->get(CommandLoaderInterface::class));

        if ($container->hasParameter('console.command.ids')) {
            foreach ($container->getParameter('console.command.ids') as $id) {
                $this->add($container->get($id));
            }
        }
    }
}
