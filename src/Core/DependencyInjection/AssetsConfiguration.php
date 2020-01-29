<?php

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class AssetsConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('assets');
        $root = $treeBuilder->getRootNode();
        $packageDefinition = $this->getPackageDefinition();

        $root
            ->append($packageDefinition)

            ->fixXmlConfig('package')

            ->children()
                ->arrayNode('packages')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)

                    ->arrayPrototype()
                        ->append($packageDefinition)
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @return NodeDefinition
     */
    private function getPackageDefinition(): NodeDefinition
    {
        $node = (new TreeBuilder('package'))->getRootNode();

        return $node
            ->fixXmlConfig('base_url')

            ->children()
                ->scalarNode('base_path')
                    ->defaultValue('')
                ->end()

                ->arrayNode('base_urls')
                    ->requiresAtLeastOneElement()

                    ->scalarPrototype()->end()

                    ->beforeNormalization()
                        ->castToArray()
                    ->end()
                ->end()

                ->scalarNode('version')->end()

                ->scalarNode('version_format')
                    ->cannotBeEmpty()
                    ->defaultValue('%%s?%%s')
                ->end()

                ->scalarNode('json_manifest_path')->end()
            ->end()

            ->validate()
                ->ifTrue(static function (array $v): bool {
                    return '' !== $v['base_path'] && $v['base_urls'];
                })
                ->thenInvalid('Cannot have both a "base_path" and "base_urls" for a package.')
            ->end()

            ->validate()
                ->ifTrue(static function (array $v): bool {
                    return isset($v['version'], $v['json_manifest_path']);
                })
                ->thenInvalid('Cannot specify both a "version" and a "json_manifest_path" for a package.')
            ->end();
    }
}
