<?php

declare(strict_types=1);

namespace App\Common\Filesystem;

use League\Flysystem\Filesystem;
use League\Flysystem\PathNormalizer;
use League\Flysystem\WhitespacePathNormalizer;

class PublicFilesystem extends Filesystem implements PublicFilesystemOperator
{
    private PublicFilesystemAdapter $adapter;
    private PathNormalizer $pathNormalizer;

    /**
     * @param PublicFilesystemAdapter $adapter
     * @param array                   $config
     * @param PathNormalizer|null     $pathNormalizer
     */
    public function __construct(
        PublicFilesystemAdapter $adapter,
        array $config = [],
        PathNormalizer $pathNormalizer = null
    ) {
        parent::__construct($adapter, $config, $pathNormalizer);

        $this->adapter = $adapter;
        $this->pathNormalizer = $pathNormalizer ?? new WhitespacePathNormalizer();
    }

    /**
     * {@inheritDoc}
     */
    public function publicUrl(string $path): string
    {
        return $this->adapter->publicUrl($this->pathNormalizer->normalizePath($path));
    }
}
