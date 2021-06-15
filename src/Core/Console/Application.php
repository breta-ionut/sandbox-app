<?php

declare(strict_types=1);

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
    private bool $commandsRegistered = false;

    /**
     * @var \Throwable[]
     */
    private array $registrationErrors = [];

    public function __construct(private KernelInterface $kernel, string $name, string $version)
    {
        parent::__construct($name, $version);

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

    public function getKernel(): KernelInterface
    {
        return $this->kernel;
    }

    /**
     * {@inheritDoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output): int
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
    public function getLongVersion(): string
    {
        return parent::getLongVersion()
            .\sprintf(
                ' (env: <comment>%s</comment>, debug: <comment>%s</comment>)',
                $this->kernel->getEnvironment(),
                $this->kernel->isDebug() ? 'true' : 'false'
            );
    }

    /**
     * {@inheritDoc}
     */
    public function add(Command $command): ?Command
    {
        $this->registerCommands();

        return parent::add($command);
    }

    /**
     * {@inheritDoc}
     */
    public function get($name): Command
    {
        $this->registerCommands();

        return parent::get($name);
    }

    /**
     * {@inheritDoc}
     */
    public function has($name): bool
    {
        $this->registerCommands();

        return parent::has($name);
    }

    /**
     * {@inheritDoc}
     */
    public function find($name): Command
    {
        $this->registerCommands();

        return parent::find($name);
    }

    /**
     * {@inheritDoc}
     */
    public function all(string $namespace = null): array
    {
        $this->registerCommands();

        return parent::all($namespace);
    }

    /**
     * @throws LogicException
     */
    private function registerServiceAsCommand(ContainerInterface $container, string $id): void
    {
        if (!$container->has($id)) {
            throw new LogicException(\sprintf('No service with id "%s" found to register as a command.', $id));
        }

        $command = $container->get($id);
        if (!$command instanceof Command) {
            throw new LogicException(\sprintf('Service "%s" isn\'t a valid command.', $id));
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
