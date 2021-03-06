<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine;

use Doctrine\ORM\Tools\Console\Command\MappingDescribeCommand as BaseMappingDescribeCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MappingDescribeCommand extends Command
{
    use DoctrineCommandTrait;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:mapping:describe';

    private BaseMappingDescribeCommand $command;

    /**
     * {@inheritDoc}
     */
    public function setApplication(Application $application = null): void
    {
        parent::setApplication($application);

        $this->command->setApplication($application);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->command = new BaseMappingDescribeCommand();

        $this->setName(self::$defaultName)
            ->setAliases([])
            ->setDefinition($this->command->getDefinition())
            ->setDescription($this->command->getDescription())
            ->setHelp($this->command->getHelp());
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return $this->command->execute($input, $output);
    }

    /**
     * {@inheritDoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->command->interact($input, $output);
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->command->initialize($input, $output);
    }
}
