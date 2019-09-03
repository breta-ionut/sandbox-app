<?php

namespace App\Core\Templating;

use Symfony\Component\Templating\Helper\SlotsHelper;
use Symfony\Component\Templating\Loader\FilesystemLoader;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;

class EngineFactory
{
    /**
     * @var string
     */
    private $projectDir;

    /**
     * @param string $projectDir
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * @return PhpEngine
     */
    public function factory(): PhpEngine
    {
        $loader = new FilesystemLoader($this->projectDir.'/templates/%name%');

        return new PhpEngine(new TemplateNameParser(), $loader, [new SlotsHelper()]);
    }
}
