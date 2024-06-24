<?php

declare(strict_types=1);

use OAuth\Server\Config;
use OAuth\Server\Handler;
use OAuth\Server\HandlerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('oauth_server.config', Config::class)
        ->args([
            param('oauth_server.config.options'),
        ])
        ->alias(Config::class, 'oauth_server.config')
    ;

    $services
        ->set('oauth_server.handler', Handler::class)
        ->args([
            service('event_dispatcher'),

            service('oauth_server.bearer_token.chain_extractor'),

            service('oauth_server.token_generator.mt_rand'),

            service('oauth_server.config'),

            service('oauth_server.doctrine_storage.client'),
            service('oauth_server.doctrine_storage.access_token'),
            service('oauth_server.doctrine_storage.refresh_token'),
            service('oauth_server.doctrine_storage.auth_code'),

            service('oauth_server.grant_extension.auth_code'),
            service('oauth_server.grant_extension.client_credentials'),
            service('oauth_server.grant_extension.refresh_token'),
            service('oauth_server.grant_extension.user_credentials'),
            service('oauth_server.grant_extension.custom'),

            service('oauth_server.config'),
        ])
        ->alias(Handler::class, 'oauth_server.handler')
        ->alias(HandlerInterface::class, 'oauth_server.handler')
    ;
};
