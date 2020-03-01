<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine\ORM;

use App\Core\Command\Doctrine\DoctrineCommandTrait;
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

    /**
     * @var MappingDescribeCommand
     */
    private $command;

    /**
     * {@inheritDoc}
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);

        $this->command->setApplication($application);
    }

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->command = new BaseMappingDescribeCommand();

        $this->setName(self::$defaultName)
            ->setAliases([])
            ->setHelp($this->command->getHelp())
            ->setDefinition($this->command->getDefinition())
            ->setDescription($this->command->getDescription());
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return $this->command->execute($input, $output);
    }

    /**
     * {@inheritDoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->command->interact($input, $output);
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->command->initialize($input, $output);
    }
}
