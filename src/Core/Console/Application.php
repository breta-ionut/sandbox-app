<?php

namespace App\Core\Console;

use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class Application extends BaseApplication
{
    /**
     * @var KernelInterface
     */
    private KernelInterface $kernel;

    /**
     * @var bool
     */
    private bool $commandsRegistered = false;

    /**
     * @var \Throwable[]
     */
    private array $registrationErrors = [];

    /**
     * @param KernelInterface $kernel
     * @param string          $name
     * @param string          $version
     */
    public function __construct(KernelInterface $kernel, string $name, string $version)
    {
        parent::__construct($name, $version);

        $this->kernel = $kernel;

        $definition = $this->getDefinition();
        $definition->addOption(
            new InputOption(
                '--env',
                '-e',
                InputOption::VALUE_REQUIRED,
                'The environment name.',
                $kernel->getEnvironment()
            )
        );
        $definition->addOption(
            new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.')
        );
    }

    /**
     * @return KernelInterface
     */
    public function getKernel(): KernelInterface
    {
        return $this->kernel;
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->registerCommands();
        $this->setDispatcher($this->kernel->getContainer()->get(EventDispatcherInterface::class));

        if ($this->registrationErrors) {
            $this->renderRegistrationErrors($input, $output);
            $this->registrationErrors = [];
        }

        return parent::doRun($input, $output);
    }

    /**
     * {@inheritDoc}
     */
    public function getLongVersion()
    {
        return parent::getLongVersion()
            .sprintf(
                ' (env: <comment>%s</comment>, debug: <comment>%s</comment>)',
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
    public function all(string $namespace = null)
    {
        $this->registerCommands();

        return parent::all($namespace);
    }

    /**
     * @param ContainerInterface $container
     * @param string             $id
     *
     * @throws LogicException
     */
    private function registerServiceAsCommand(ContainerInterface $container, string $id): void
    {
        if (!$container->has($id)) {
            throw new LogicException(sprintf('No service with id "%s" found to register as a command.', $id));
        }

        $command = $container->get($id);
        if (!$command instanceof Command) {
            throw new LogicException(sprintf('Service "%s" isn\'t a valid command.', $id));
        }

        $this->add($command);
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
                try {
                    $this->registerServiceAsCommand($container, $id);
                } catch (\Throwable $exception) {
                    $this->registrationErrors[] = $exception;
                }
            }
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function renderRegistrationErrors(InputInterface $input, OutputInterface $output): void
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        $style = new SymfonyStyle($input, $output);
        $style->warning('Some commands could not be registered:');

        foreach ($this->registrationErrors as $registrationError) {
            $this->doRenderThrowable($registrationError, $output);
        }
    }
}
