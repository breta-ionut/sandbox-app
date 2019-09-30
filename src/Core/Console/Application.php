<?php

namespace App\Core\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
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

    private function registerCommands(): void
    {
        if ($this->commandsRegistered) {
            return;
        }

        $this->commandsRegistered = true;

        $this->kernel->boot();
        $container = $this->kernel->getContainer();

        $this->setCommandLoader($container->get(CommandLoaderInterface::class));

        foreach ($container->getParameter('console.command.ids') as $id) {
            $this->add($container->get($id));
        }
    }
}
