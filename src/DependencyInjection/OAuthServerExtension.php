<?php

declare(strict_types=1);

namespace OAuth\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class OAuthServerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $container->setParameter('oauth_server.config.options', $config['options']);
        $container->setParameter('oauth_server.config.client_class', $config['client_class']);
        $container->setParameter('oauth_server.config.access_token_class', $config['access_token_class']);
        $container->setParameter('oauth_server.config.refresh_token_class', $config['refresh_token_class']);
        $container->setParameter('oauth_server.config.auth_code_class', $config['auth_code_class']);

        $container->setAlias('oauth_server.user_provider', new Alias($config['user_provider'], false));

        $loader->load('doctrine_storage.php');
        $loader->load('grant_extension.php');
        $loader->load('bearer_token.php');
        $loader->load('token_generator.php');
        $loader->load('handler.php');
        $loader->load('command.php');
        $loader->load('security.php');
    }

    public function getAlias(): string
    {
        return 'oauth_server';
    }
}
