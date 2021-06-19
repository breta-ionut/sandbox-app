<?php

declare(strict_types=1);

namespace App\Core\Templating\Helper;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Templating\Helper\Helper;

class AssetsHelper extends Helper
{
    public function __construct(private Packages $packages)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'assets';
    }

    public function getUrl(string $path, string $packageName = null): string
    {
        return $this->packages->getUrl($path, $packageName);
    }
}
