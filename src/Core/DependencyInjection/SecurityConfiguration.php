<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy;

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
        $this->addFirewallsSection($root);
        $this->addAccessControlSection($root);
        $this->addOtherSettingsSection($root);

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

                            ->booleanNode('anonymous')
                                ->defaultFalse()
                            ->end()

                            ->booleanNode('stateless')
                                ->defaultTrue()
                            ->end()

                            ->scalarNode('access_denied_url')->end()

                            ->scalarNode('access_denied_handler')
                                ->cannotBeEmpty()
                            ->end()

                            ->arrayNode('logout')
                                ->fixXmlConfig('clear_cookie')
                                ->fixXmlConfig('handler')
                                ->canBeEnabled()

                                ->children()
                                    ->scalarNode('path')
                                        ->cannotBeEmpty()
                                    ->end()

                                    ->scalarNode('csrf_parameter')
                                        ->cannotBeEmpty()
                                    ->end()

                                    ->scalarNode('csrf_token_id')
                                        ->cannotBeEmpty()
                                    ->end()

                                    ->scalarNode('target')
                                        ->defaultValue('/')
                                    ->end()

                                    ->scalarNode('success_handler')
                                        ->cannotBeEmpty()
                                    ->end()

                                    ->booleanNode('invalidate_session')
                                        ->defaultTrue()
                                    ->end()

                                    ->arrayNode('clear_cookies')
                                        ->useAttributeAsKey('name')
                                        ->normalizeKeys(false)

                                        ->beforeNormalization()
                                            ->ifTrue(static function ($value): bool {
                                                return \is_string($value)
                                                    || (\is_array($value)
                                                        && \is_int(\array_key_first($value))
                                                        && \is_string(\reset($value)
                                                    ));
                                            })
                                            ->then(static function ($value): array {
                                                return \array_map(static function (string $value): array {
                                                    return ['name' => $value];
                                                }, (array) $value);
                                            })
                                        ->end()

                                        ->arrayPrototype()
                                            ->children()
                                                ->scalarNode('path')
                                                    ->defaultNull()
                                                ->end()

                                                ->scalarNode('domain')
                                                    ->defaultNull()
                                                ->end()

                                                ->booleanNode('secure')->end()

                                                ->enumNode('samesite')
                                                    ->values([
                                                        null,
                                                        Cookie::SAMESITE_NONE,
                                                        Cookie::SAMESITE_LAX,
                                                        Cookie::SAMESITE_STRICT,
                                                    ])
                                                ->end()
                                            ->end()
                                        ->end()
                                    ->end()

                                    ->arrayNode('handlers')
                                        ->scalarPrototype()
                                            ->cannotBeEmpty()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()

                            ->scalarNode('watchdog')
                                ->cannotBeEmpty()
                            ->end()

                            ->scalarNode('user_provider')
                                ->cannotBeEmpty()
                            ->end()

                            ->scalarNode('user_checker')
                                ->cannotBeEmpty()
                            ->end()
                        ->end()

                        ->validate()
                            ->ifTrue(static function (array $value): bool {
                                return isset($value['watchdog']) && !isset($value['user_provider']);
                            })
                            ->thenInvalid(
                                'In order to use watchdog authentication, a user provider must also be declared.'
                            )
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

    /**
     * @param ArrayNodeDefinition $root
     */
    private function addOtherSettingsSection(ArrayNodeDefinition $root): void
    {
        $root
            ->children()
                ->booleanNode('erase_credentials')
                    ->defaultTrue()
                ->end()

                ->enumNode('session_authentication_strategy')
                    ->values([
                        SessionAuthenticationStrategy::NONE,
                        SessionAuthenticationStrategy::MIGRATE,
                        SessionAuthenticationStrategy::INVALIDATE,
                    ])
                    ->defaultValue(SessionAuthenticationStrategy::MIGRATE)
                ->end()

                ->scalarNode('anonymous_secret')->end()

                ->booleanNode('always_authenticate_before_granting')
                    ->defaultFalse()
                ->end()

                ->arrayNode('access_decision_manager')
                    ->addDefaultsIfNotSet()

                    ->children()
                        ->enumNode('strategy')
                            ->values([
                                AccessDecisionManager::STRATEGY_AFFIRMATIVE,
                                AccessDecisionManager::STRATEGY_CONSENSUS,
                                AccessDecisionManager::STRATEGY_UNANIMOUS,
                            ])
                            ->defaultValue(AccessDecisionManager::STRATEGY_AFFIRMATIVE)
                        ->end()

                        ->booleanNode('allow_if_all_abstain')
                            ->defaultFalse()
                        ->end()

                        ->booleanNode('allow_if_equal_granted_denied')
                            ->defaultTrue()
                        ->end()
                    ->end()
                ->end()

                ->scalarNode('http_port')
                    ->defaultValue(80)
                ->end()

                ->scalarNode('https_port')
                    ->defaultValue(443)
                ->end()
            ->end();
    }
}
