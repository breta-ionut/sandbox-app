<?php

namespace App\Core\Command\Doctrine;

use Doctrine\Migrations\Configuration\Configuration;
use Doctrine\Migrations\Tools\Console\Command\AbstractCommand as AbstractDoctrineMigrationsCommand;
use Doctrine\Migrations\Tools\Console\Helper\ConfigurationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Helper\HelperSet;

trait DoctrineCommandTrait
{
    /**
     * Adds to the application's helper set additional helpers needed by Doctrine commands.
     *
     * @param HelperSet $helperSet
     */
    public function setHelperSet(HelperSet $helperSet)
    {
        /** @var ContainerInterface $container */
        $container = $this->getApplication()
            ->getKernel()
            ->getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);

        foreach (ConsoleRunner::createHelperSet($entityManager) as $alias => $helper) {
            $helperSet->set($helper, $alias);
        }

        // Additional helpers are needed by Doctrine Migrations commands.
        if ($this instanceof AbstractDoctrineMigrationsCommand) {
            $helperSet->set(
                new ConfigurationHelper($entityManager->getConnection(), $container->get(Configuration::class)),
                'configuration'
            );
        }

        parent::setHelperSet($helperSet);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName(self::$defaultName)
            ->setAliases([]);
    }
}
