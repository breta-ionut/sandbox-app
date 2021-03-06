<?php

declare(strict_types=1);

namespace App\Core\Command\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DropDatabaseCommand extends Command
{
    private const NO_FORCE = 3;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:database:drop';

    public function __construct(private Connection $connection)
    {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Drops the application\'s designated database.')
            ->addOption(
                'if-exists',
                null,
                InputOption::VALUE_NONE,
                'If the command shouldn\'t be sent to the RDBMS when the database doesn\'t exist.'
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'The operation\'s safety catch. Only passing this option will trigger the database drop.'
            )
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command drops the application's designated database.

    <info>%command.full_name%</info>

The <info>--force</info> option should be passed to actually drop the database.

<error>Caution: All application's data will be lost by running this command.</error>
EOT
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $params = $this->connection->getParams();
        $isFile = isset($params['path']);

        $name = $isFile ? $params['path'] : ($params['dbname'] ?? null);
        if (null === $name) {
            $style->error('A "path" or "dbname" connection parameter is required to determine the database to drop.');

            return self::FAILURE;
        }

        if (!$input->getOption('force')) {
            $style->caution([
                'This operation should not be executed in a production environment.',
                sprintf('Would drop the database "%s".', $name),
                'Run the command with --force for executing the operation.',
                'All data will be lost!',
            ]);

            return self::NO_FORCE;
        }

        // Strip all references to the database name from the parameters before initiating the connection, an error
        // might be raised when trying to drop the database being used.
        unset($params['dbname'], $params['url']);
        $connection = DriverManager::getConnection($params);

        $schemaManager = $connection->getSchemaManager();
        $dropDatabase = !$input->getOption('if-exists') || \in_array($name, $schemaManager->listDatabases(), true);
        $escapedName = !$isFile ? $connection->getDatabasePlatform()->quoteSingleIdentifier($name) : $name;

        try {
            if ($dropDatabase) {
                $schemaManager->dropDatabase($escapedName);

                $style->success(\sprintf('Dropped database "%s".', $name));
            } else {
                $style->success(\sprintf('Database "%s" doesn\'t exist.', $name));
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $style->error([\sprintf('Error occurred while dropping database "%s":', $name), $exception->getMessage()]);

            return self::FAILURE;
        } finally {
            $connection->close();
        }
    }
}
