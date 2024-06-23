<?php

declare(strict_types=1);

namespace OAuth\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AuthenticatorFactoryInterface;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class OAuthFactory implements AuthenticatorFactoryInterface
{
    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId): string
    {
        $authenticatorId = 'security.authenticator.oauth.' . $firewallName;
        $firewallEventDispatcherId = 'security.event_dispatcher.' . $firewallName;

        $firewallAuthenticationProviders = [];
        $authenticators = array_map(static fn ($firewallName) => new Reference($firewallName), $firewallAuthenticationProviders);

        $container
            ->setDefinition($managerId = 'security.authenticator.oauth.' . $firewallName, new ChildDefinition('oauth_server.security.authenticator'))
            ->addTag('monolog.logger', ['channel' => 'security'])
        ;

        $managerLocator = $container->getDefinition('security.authenticator.managers_locator');
        $managerLocator->replaceArgument(0, array_merge($managerLocator->getArgument(0), [$firewallName => new ServiceClosureArgument(new Reference($managerId))]));

        $container
            ->setDefinition('security.firewall.authenticator.' . $firewallName, new ChildDefinition('security.firewall.authenticator'))
            ->replaceArgument(0, new Reference($managerId))
        ;

        $container
            ->setDefinition('security.listener.user_checker.' . $firewallName, new ChildDefinition('security.listener.user_checker'))
            ->replaceArgument(0, new Reference('security.user_checker.' . $firewallName))
            ->addTag('kernel.event_subscriber', ['dispatcher' => $firewallEventDispatcherId])
        ;

        if ($container->hasDefinition('security.command.debug_firewall')) {
            $debugCommand = $container->getDefinition('security.command.debug_firewall');
            $debugCommand->replaceArgument(3, array_merge($debugCommand->getArgument(3), [$firewallName => $authenticators]));
        }

        return $authenticatorId;
    }

    public function getKey(): string
    {
        return 'oauth';
    }

    public function addConfiguration(NodeDefinition $builder): void
    {
    }

    public function getPriority(): int
    {
        return 0;
    }
}
