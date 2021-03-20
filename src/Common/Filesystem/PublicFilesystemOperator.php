<?php

declare(strict_types=1);

namespace App\Common\Filesystem;

use League\Flysystem\FilesystemOperator;

interface PublicFilesystemOperator extends FilesystemOperator
{
    /**
     * @param string $path
     *
     * @return string
     */
    public function publicUrl(string $path): string;
}
