<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class SecurityConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('security');
        $root = $treeBuilder->getRootNode();

        $this->addEncodersSection($root);
        $this->addAccessControlSection($root);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $root
     */
    private function addEncodersSection(ArrayNodeDefinition $root): void
    {
        $root
            ->fixXmlConfig('encoder')

            ->children()
                ->arrayNode('encoders')
                    ->useAttributeAsKey('class')
                    ->normalizeKeys(false)

                    ->arrayPrototype()
                        ->info(\sprintf(
                            'See %s::getEncoderConfigFromAlgorithm() on how to configure the encoders.',
                            EncoderFactory::class
                        ))

                        ->beforeNormalization()
                            ->ifString()
                            ->then(static function (string $value): array {
                                return ['algorithm' => $value];
                            })
                        ->end()

                        ->children()
                            ->scalarNode('algorithm')
                                ->cannotBeEmpty()
                            ->end()

                            ->arrayNode('migrate_from')
                                ->beforeNormalization()
                                    ->castToArray()
                                ->end()

                                ->scalarPrototype()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()

                            ->booleanNode('ignore_case')
                                ->defaultFalse()
                            ->end()

                            ->scalarNode('hash_algorithm')
                                ->info('See hash_algos() for a list of supported algorithms.')

                                ->cannotBeEmpty()
                                ->defaultValue('sha512')
                            ->end()

                            ->booleanNode('encode_as_base64')->end()

                            ->integerNode('iterations')
                                ->min(1)
                            ->end()

                            ->integerNode('key_length')
                                ->min(0)
                            ->end()

                            ->integerNode('time_cost')
                                ->min(3)
                            ->end()

                            ->integerNode('memory_cost')
                                ->min(10)
                            ->end()

                            ->integerNode('cost')
                                ->min(4)
                                ->max(31)
                            ->end()

                            ->scalarNode('native_algorithm')
                                ->info('An algorithm accepted by password_hash().')

                                ->cannotBeEmpty()
                            ->end()

                            ->scalarNode('id')
                                ->info('The id of a custom password encoder.')

                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $root
     */
    private function addFirewallsSection(ArrayNodeDefinition $root): void
    {
        $root
            ->fixXmlConfig('firewall')

            ->children()
                ->arrayNode('firewalls')
                    ->useAttributeAsKey('name')
                    ->normalizeKeys(false)

                    ->arrayPrototype()
                        ->fixXmlConfig('method')
                        ->fixXmlConfig('ip')
                        ->fixXmlConfig('attribute')

                        ->children()
                            ->scalarNode('path')
                                ->cannotBeEmpty()
                            ->end()

                            ->scalarNode('host')->end()

                            ->scalarNode('port')->end()

                            ->arrayNode('methods')
                                ->requiresAtLeastOneElement()

                                ->beforeNormalization()
                                    ->castToArray()
                                ->end()

                                ->scalarPrototype()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()

                            ->arrayNode('ips')
                                ->requiresAtLeastOneElement()

                                ->beforeNormalization()
                                    ->castToArray()
                                ->end()

                                ->scalarPrototype()->end()
                            ->end()

                            ->arrayNode('attributes')
                                ->requiresAtLeastOneElement()
                                ->useAttributeAsKey('name')
                                ->normalizeKeys(false)

                                ->scalarPrototype()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()

                            ->enumNode('scheme')
                                ->values(['http', 'https'])
                            ->end()

                            ->booleanNode('security')
                                ->defaultTrue()
                            ->end()

                            ->arrayNode('anonymous')
                                ->canBeEnabled()

                                ->children()
                                    ->scalarNode('secret')
                                        ->cannotBeEmpty()
                                    ->end()
                                ->end()
                            ->end()

                            ->booleanNode('stateless')
                                ->defaultTrue()
                            ->end()

                            ->scalarNode('access_denied_url')->end()

                            ->scalarNode('access_denied_handler')
                                ->cannotBeEmpty()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $root
     */
    private function addAccessControlSection(ArrayNodeDefinition $root): void
    {
        $root
            ->fixXmlConfig('rule', 'access_control')

            ->children()
                ->arrayNode('access_control')
                    ->cannotBeOverwritten()

                    ->arrayPrototype()
                        ->fixXmlConfig('method')
                        ->fixXmlConfig('ip')
                        ->fixXmlConfig('attribute')
                        ->fixXmlConfig('role')

                        ->children()
                            ->scalarNode('path')
                                ->cannotBeEmpty()
                            ->end()

                            ->scalarNode('host')->end()

                            ->scalarNode('port')->end()

                            ->arrayNode('methods')
                                ->requiresAtLeastOneElement()

                                ->beforeNormalization()
                                    ->castToArray()
                                ->end()

                                ->scalarPrototype()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()

                            ->arrayNode('ips')
                                ->requiresAtLeastOneElement()

                                ->beforeNormalization()
                                    ->castToArray()
                                ->end()

                                ->scalarPrototype()->end()
                            ->end()

                            ->arrayNode('attributes')
                                ->requiresAtLeastOneElement()
                                ->useAttributeAsKey('name')
                                ->normalizeKeys(false)

                                ->scalarPrototype()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()

                            ->enumNode('scheme')
                                ->values(['http', 'https'])
                            ->end()

                            ->arrayNode('roles')
                                ->requiresAtLeastOneElement()

                                ->beforeNormalization()
                                    ->castToArray()
                                ->end()

                                ->scalarPrototype()
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()

                            ->enumNode('channel')
                                ->values(['http', 'https'])
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
