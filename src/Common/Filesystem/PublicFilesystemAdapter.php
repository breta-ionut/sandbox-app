<?php

declare(strict_types=1);

namespace App\Common\Filesystem;

use League\Flysystem\FilesystemAdapter;

interface PublicFilesystemAdapter extends FilesystemAdapter
{
    /**
     * @param string $path
     *
     * @return string
     */
    public function publicUrl(string $path): string;
}
