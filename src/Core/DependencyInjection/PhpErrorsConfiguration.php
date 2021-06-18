<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class PhpErrorsConfiguration implements ConfigurationInterface
{
    public function __construct(private bool $debug)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('php_errors');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->scalarNode('log')
                    ->info(
                        'Whether to use or not the application logger to log PHP errors or for which levels to use it. '
                        .'The levels must be specified as an integer bitmask of E_* constants.'
                    )

                    ->defaultValue($this->debug)
                    ->treatNullLike($this->debug)

                    ->validate()
                        ->ifTrue(static fn($value): bool => !\is_bool($value) || !\is_int($value))
                        ->thenInvalid('Expected either a boolean or an integer.')
                    ->end()
                ->end()

                ->booleanNode('throw')
                    ->info('Whether to convert PHP errors to exceptions.')

                    ->defaultValue($this->debug)
                    ->treatNullLike($this->debug)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
