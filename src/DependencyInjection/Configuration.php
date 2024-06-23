<?php

declare(strict_types=1);

namespace OAuth\DependencyInjection;

use OAuth\Model\AccessTokenInterface;
use OAuth\Model\AuthCodeInterface;
use OAuth\Model\ClientInterface;
use OAuth\Model\RefreshTokenInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('oauth_server');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->scalarNode('client_class')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->always(function ($v) {
                            if (!is_subclass_of($v, ClientInterface::class)) {
                                throw new \InvalidArgumentException(sprintf('The client class must implement %s', ClientInterface::class));
                            }

                            return $v;
                        })
                    ->end()
                ->end()
                ->scalarNode('access_token_class')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                    ->always(function ($v) {
                        if (!is_subclass_of($v, AccessTokenInterface::class)) {
                            throw new \InvalidArgumentException(sprintf('The client class must implement %s', AccessTokenInterface::class));
                        }

                        return $v;
                    })
                    ->end()
                ->end()
                ->scalarNode('refresh_token_class')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->always(function ($v) {
                            if (!is_subclass_of($v, RefreshTokenInterface::class)) {
                                throw new \InvalidArgumentException(sprintf('The client class must implement %s', RefreshTokenInterface::class));
                            }

                            return $v;
                        })
                    ->end()
                ->end()
                ->scalarNode('auth_code_class')
                    ->isRequired()
                    ->cannotBeEmpty()
                    ->validate()
                        ->always(function ($v) {
                            if (!is_subclass_of($v, AuthCodeInterface::class)) {
                                throw new \InvalidArgumentException(sprintf('The client class must implement %s', AuthCodeInterface::class));
                            }

                            return $v;
                        })
                    ->end()
                ->end()
                ->scalarNode('user_provider')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('options')
                    ->treatNullLike([])
                    ->useAttributeAsKey('key')
                    ->prototype('variable')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
