<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use OAuth\Doctrine\Storage\AccessTokenStorage;
use OAuth\Doctrine\Storage\AuthCodeStorage;
use OAuth\Doctrine\Storage\ClientStorage;
use OAuth\Doctrine\Storage\RefreshTokenStorage;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('oauth_server.entity_manager', EntityManager::class)
        ->args([
            'default',
        ])
        ->factory([service('doctrine'), 'getManager'])
        ->private()
    ;

    $services
        ->set('oauth_server.doctrine_storage.access_token', AccessTokenStorage::class)
        ->args([
            service('oauth_server.entity_manager'),
            param('oauth_server.config.access_token_class'),
        ])
        ->alias(AccessTokenStorage::class, 'oauth_server.doctrine_storage.access_token')
    ;

    $services
        ->set('oauth_server.doctrine_storage.auth_code', AuthCodeStorage::class)
        ->args([
            service('oauth_server.entity_manager'),
            param('oauth_server.config.auth_code_class'),
        ])
        ->alias(AuthCodeStorage::class, 'oauth_server.doctrine_storage.auth_code')
    ;

    $services
        ->set('oauth_server.doctrine_storage.client', ClientStorage::class)
        ->args([
            service('oauth_server.entity_manager'),
            param('oauth_server.config.client_class'),
        ])
        ->alias(ClientStorage::class, 'oauth_server.doctrine_storage.client')
    ;

    $services
        ->set('oauth_server.doctrine_storage.refresh_token', RefreshTokenStorage::class)
        ->args([
            service('oauth_server.entity_manager'),
            param('oauth_server.config.refresh_token_class'),
        ])
        ->alias(RefreshTokenStorage::class, 'oauth_server.doctrine_storage.refresh_token')
    ;
};
