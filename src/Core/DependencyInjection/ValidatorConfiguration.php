<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Validator\Constraints\Email;

class ValidatorConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('validator');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->enumNode('email_validation_mode')
                    ->values([
                        Email::VALIDATION_MODE_LOOSE,
                        Email::VALIDATION_MODE_STRICT,
                        Email::VALIDATION_MODE_HTML5,
                    ])
                    ->defaultValue(Email::VALIDATION_MODE_LOOSE)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
