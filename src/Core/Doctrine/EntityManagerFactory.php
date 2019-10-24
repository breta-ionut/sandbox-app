<?php

namespace App\Core\Doctrine;

use Doctrine\Common\Persistence\Mapping\Driver\SymfonyFileLocator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class EntityManagerFactory
{
    private const MAPPING_DIR_PATTERN = '%s/doctrine';
    private const NAMESPACE_PREFIX_PATTERN = 'App\\%s\\Model';

    private const PROXY_DIR_PATTERN = '%s/doctrine/Proxies';

    /**
     * @var array
     */
    private $connectionParams;

    /**
     * @var string
     */
    private $configDir;

    /**
     * @var RepositoryFactory
     */
    private $repositoryFactory;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var string
     */
    private $environment;

    /**
     * @param array             $connectionParams
     * @param string            $configDir
     * @param RepositoryFactory $repositoryFactory
     * @param EventManager      $eventManager
     * @param string            $cacheDir
     * @param string            $environment
     */
    public function __construct(
        array $connectionParams,
        string $configDir,
        RepositoryFactory $repositoryFactory,
        EventManager $eventManager,
        string $cacheDir,
        string $environment
    ) {
        $this->connectionParams = $connectionParams;
        $this->configDir = $configDir;
        $this->repositoryFactory = $repositoryFactory;
        $this->eventManager = $eventManager;
        $this->cacheDir = $cacheDir;
        $this->environment = $environment;
    }

    /**
     * @return EntityManager
     */
    public function factory(): EntityManager
    {
        $isDevMode = in_array($this->environment, ['dev', 'test']);
        $proxyDir = sprintf(self::PROXY_DIR_PATTERN, $this->cacheDir);

        // Determine the associations between the mapping files and the entities of the application.
        $prefixes = [];
        $mappingDir = sprintf(self::MAPPING_DIR_PATTERN, $this->configDir);

        if (is_dir($mappingDir)) {
            $finder = (new Finder())
                ->in($mappingDir)
                ->depth(0)
                ->directories();

            /** @var SplFileInfo $file */
            foreach ($finder as $file) {
                $prefixes[$file->getRealPath()] = sprintf(self::NAMESPACE_PREFIX_PATTERN, $file);
            }
        }

        $config = Setup::createConfiguration($isDevMode, $proxyDir);
        $config->setMetadataDriverImpl(new XmlDriver(new SymfonyFileLocator($prefixes)));
        $config->setRepositoryFactory($this->repositoryFactory);

        return EntityManager::create($this->connectionParams, $config, $this->eventManager);
    }
}
