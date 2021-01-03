<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine;

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

        parent::setHelperSet($helperSet);
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName(self::$defaultName)
            ->setAliases([]);
    }
}
