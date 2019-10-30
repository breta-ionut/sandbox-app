<?php

namespace App\Core\Command\Doctrine\ORM;

use App\Core\Command\Doctrine\DoctrineCommandTrait;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateDatabaseCommand extends Command
{
    use DoctrineCommandTrait;

    private const CODE_ERROR = 1;

    /**
     * {@inheritDoc}
     */
    protected static $defaultName = 'doctrine:database:create';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct();

        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Creates the application\'s designated database.')
            ->addOption(
                'if-not-exists',
                null,
                InputOption::VALUE_NONE,
                'If the command shouldn\'t be sent to the RDBMS when the database already exists.'
            )
            ->setHelp(<<<'EOT'
The <info>%command.name%</info> command creates the application's designated database.

    <info>%command.full_name%</info>
EOT
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $style = new SymfonyStyle($input, $output);

        $params = $this->connection->getParams();
        $isFile = isset($params['path']);

        $name = $isFile ? $params['path'] : ($params['dbname'] ?? null);
        if (null === $name) {
            $style->error(
                'Expecting either a <comment>path</comment> or a <comment>dbname</comment> connection parameter in '
                .'order to determine the name of the database to create.'
            );

            return self::CODE_ERROR;
        }

        // Strip all references to the database name from the parameters before initiating the connection, in order to
        // prevent errors if the database doesn't exist.
        unset($params['path'], $params['dbname'], $params['url']);
        $connection = DriverManager::getConnection($params);
        $schemaManager = $connection->getSchemaManager();

        $createDatabase = !$input->getOption('if-not-exists')
            || !in_array($name, $schemaManager->listDatabases(), true);

        if (!$isFile) {
            $name = $connection->getDatabasePlatform()->quoteSingleIdentifier($name);
        }

        try {
            if ($createDatabase) {
                $schemaManager->createDatabase($name);

                $style->success(sprintf('Successfully created database <comment>%s</comment>.', $name));
            } else {
                $style->success(sprintf('Database <comment>%s</comment> already exists.', $name));
            }
        } catch (\Throwable $exception) {
            $style->error(sprintf('An error occurred while creating the <comment>%s</comment> database.', $name));
            $style->error($exception->getMessage());

            return self::CODE_ERROR;
        } finally {
            $connection->close();
        }
    }
}
