<?php

namespace App\Core\Templating\Helper;

use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Templating\Helper\Helper;

class AssetsHelper extends Helper
{
    /**
     * @var PackageInterface
     */
    private $package;

    /**
     * @param PackageInterface $package
     */
    public function __construct(PackageInterface $package)
    {
        $this->package = $package;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'assets';
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getUrl(string $path): string
    {
        return $this->package->getUrl($path);
    }
}
