<?php

namespace App\Core\Templating\Helper;

use Symfony\Component\Asset\Packages;
use Symfony\Component\Templating\Helper\Helper;

class AssetsHelper extends Helper
{
    /**
     * @var Packages
     */
    private Packages $packages;

    /**
     * @param Packages $packages
     */
    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'assets';
    }

    /**
     * @param string      $path
     * @param string|null $packageName
     *
     * @return string
     */
    public function getUrl(string $path, string $packageName = null): string
    {
        return $this->packages->getUrl($path, $packageName);
    }
}
