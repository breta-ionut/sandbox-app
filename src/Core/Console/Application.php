<?php

namespace App\Core\Console;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\LogicException;
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
     * @var \Throwable[]
     */
    private $registrationErrors = [];

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

    /**
     * @param ContainerInterface $container
     * @param string $id
     *
     * @throws LogicException
     */
    private function registerServiceAsCommand(ContainerInterface $container, string $id): void
    {
        if (!$container->has($id)) {
            throw new LogicException(sprintf('No service with id `%s` found to register as a command.', $id));
        }

        $command = $container->get($id);
        if (!$command instanceof Command) {
            throw new LogicException(sprintf('Service `%s` isn\'t a valid command.', $id));
        }

        $this->add($command);
    }

    /**
     * @throws LogicException
     */
    private function registerCommands(): void
    {
        if ($this->commandsRegistered) {
            return;
        }

        $this->commandsRegistered = true;

        $this->kernel->boot();
        $container = $this->kernel->getContainer();

        // Try to retrieve and register the application's command loader.
        if (!$container->has(CommandLoaderInterface::class)
            || !($commandLoader = $container->get(CommandLoaderInterface::class)) instanceof CommandLoaderInterface
        ) {
            throw new LogicException(sprintf(
                'Expecting a command loader with the `%s` id in the container in order to run.',
                CommandLoaderInterface::class
            ));
        }

        /** @var CommandLoaderInterface $commandLoader */
        $this->setCommandLoader($commandLoader);

        // Try to register the commands defined as services, if any.
        if ($container->hasParameter('console.command.ids')) {
            foreach ($container->getParameter('console.command.ids') as $id) {
                try {
                    $this->registerServiceAsCommand($container, $id);
                } catch (\Throwable $exception) {
                    $this->registrationErrors[] = $exception;
                }
            }
        }
    }
}
