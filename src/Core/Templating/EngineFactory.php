<?php

declare(strict_types=1);

namespace App\Core\Templating;

use Symfony\Component\Templating\Helper\HelperInterface;
use Symfony\Component\Templating\Loader\LoaderInterface;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParserInterface;

class EngineFactory
{
    /**
     * @param iterable<HelperInterface> $helpers
     */
    public static function create(
        TemplateNameParserInterface $parser,
        LoaderInterface $loader,
        iterable $helpers,
    ): PhpEngine {
        $helpers = \is_array($helpers) ? $helpers : \iterator_to_array($helpers);

        return new PhpEngine($parser, $loader, $helpers);
    }
}
