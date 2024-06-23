<?php

declare(strict_types=1);

use OAuth\Server\GrantExtension\AuthCodeGrantExtension;
use OAuth\Server\GrantExtension\ClientCredentialsGrantExtension;
use OAuth\Server\GrantExtension\CustomGrantExtension;
use OAuth\Server\GrantExtension\RefreshTokenGrantExtension;
use OAuth\Server\GrantExtension\UserCredentialsGrantExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('oauth_server.grant_extension.auth_code', AuthCodeGrantExtension::class)
        ->args([
            service('oauth_server.doctrine_storage.auth_code'),
        ])
        ->alias(AuthCodeGrantExtension::class, 'oauth_server.grant_extension.auth_code')
    ;

    $services
        ->set('oauth_server.grant_extension.client_credentials', ClientCredentialsGrantExtension::class)
        ->alias(ClientCredentialsGrantExtension::class, 'oauth_server.grant_extension.client_credentials')
    ;

    $services
        ->set('oauth_server.grant_extension.refresh_token', RefreshTokenGrantExtension::class)
        ->args([
            service('oauth_server.doctrine_storage.refresh_token'),
        ])
        ->alias(RefreshTokenGrantExtension::class, 'oauth_server.grant_extension.refresh_token')
    ;

    $services
        ->set('oauth_server.grant_extension.user_credentials', UserCredentialsGrantExtension::class)
        ->args([
            service('oauth_server.user_provider'),
            service('security.password_hasher_factory'),
        ])
        ->alias(UserCredentialsGrantExtension::class, 'oauth_server.grant_extension.user_credentials')
    ;

    $services
        ->set('oauth_server.grant_extension.custom', CustomGrantExtension::class)
        ->alias(CustomGrantExtension::class, 'oauth_server.grant_extension.custom')
    ;
};
