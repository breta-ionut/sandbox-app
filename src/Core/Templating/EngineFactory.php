<?php

namespace App\Core\Templating;

use Symfony\Component\Templating\Helper\HelperInterface;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

class EngineFactory
{
    private const TEMPLATE_PATH_PATTERN = '/templates/%name%';

    /**
     * @var string
     */
    private string $projectDir;

    /**
     * @var HelperInterface[]|iterable
     */
    private iterable $helpers;

    /**
     * @param string                     $projectDir
     * @param HelperInterface[]|iterable $helpers
     */
    public function __construct(string $projectDir, iterable $helpers)
    {
        $this->projectDir = $projectDir;
        $this->helpers = $helpers;
    }

    /**
     * @return PhpEngine
     */
    public function factory(): PhpEngine
    {
        $loader = new FilesystemLoader($this->projectDir.self::TEMPLATE_PATH_PATTERN);
        $helpers = is_array($this->helpers) ? $this->helpers : iterator_to_array($this->helpers);

        return new PhpEngine(new TemplateNameParser(), $loader, $helpers);
    }
}
