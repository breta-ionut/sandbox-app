<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Cookie;

class HttpConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('http');
        $root = $treeBuilder->getRootNode();

        $root
            ->children()
                ->scalarNode('error_controller')
                    ->defaultNull()
                ->end()

                ->arrayNode('session')
                    ->addDefaultsIfNotSet()

                    ->children()
                        ->arrayNode('handler')
                            ->addDefaultsIfNotSet()

                            ->beforeNormalization()
                                ->ifTrue(static function (array $value): bool {
                                    return isset($value['id']);
                                })
                                ->then(static function (array $value): array {
                                    // The connection URL is ignored if a custom service is specified to serve as the
                                    // session storage handler.
                                    unset($value['url']);

                                    return $value;
                                })
                            ->end()

                            ->children()
                                ->scalarNode('id')
                                    ->cannotBeEmpty()
                                ->end()

                                ->scalarNode('url')
                                    ->info('The URL of the connection to be used by the session storage handler.')
                                    ->example('redis://localhost:6379')

                                    ->defaultValue('file://%kernel.cache_dir%/sessions')
                                ->end()
                            ->end()
                        ->end()

                        ->booleanNode('test')
                            ->defaultValue(false)
                        ->end()

                        ->scalarNode('cookie_domain')->end()

                        ->booleanNode('cookie_httponly')
                            ->defaultValue(true)
                        ->end()

                        ->integerNode('cookie_lifetime')
                            ->min(0)
                        ->end()

                        ->scalarNode('cookie_path')->end()

                        ->enumNode('cookie_secure')
                            ->values([true, false, 'auto'])
                        ->end()

                        ->enumNode('cookie_samesite')
                            ->values([Cookie::SAMESITE_NONE, Cookie::SAMESITE_LAX, Cookie::SAMESITE_STRICT, null])
                        ->end()

                        ->integerNode('gc_divisor')
                            ->min(1)
                        ->end()

                        ->integerNode('gc_maxlifetime')
                            ->min(0)
                        ->end()

                        ->integerNode('gc_probability')
                            ->min(0)
                        ->end()

                        ->scalarNode('name')
                            ->cannotBeEmpty()
                        ->end()

                        ->booleanNode('use_cookies')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
