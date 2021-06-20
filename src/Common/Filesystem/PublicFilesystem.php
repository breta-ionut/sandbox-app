<?php

declare(strict_types=1);

namespace App\Common\Filesystem;

use League\Flysystem\Filesystem;
use League\Flysystem\PathNormalizer;
use League\Flysystem\WhitespacePathNormalizer;

class PublicFilesystem extends Filesystem implements PublicFilesystemOperator
{
    private PathNormalizer $pathNormalizer;

    public function __construct(
        private PublicFilesystemAdapter $adapter,
        array $config = [],
        PathNormalizer $pathNormalizer = null,
    ) {
        parent::__construct($adapter, $config, $pathNormalizer);

        $this->pathNormalizer = $pathNormalizer ?? new WhitespacePathNormalizer();
    }

    public function publicUrl(string $path): string
    {
        return $this->adapter->publicUrl($this->pathNormalizer->normalizePath($path));
    }
}
