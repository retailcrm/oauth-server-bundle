<?php

declare(strict_types=1);

use OAuth\Security\Authenticator\OAuthAuthenticator;
use OAuth\Security\EntryPoint\OAuthEntryPoint;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('oauth_server.security.entry_point', OAuthEntryPoint::class)
        ->args([
            service('oauth_server.config'),
        ])
        ->alias(OAuthEntryPoint::class, 'oauth_server.security.entry_point')
    ;

    $services
        ->set('oauth_server.security.authenticator', OAuthAuthenticator::class)
        ->args([
            service('security.user_checker'),
            service('oauth_server.handler'),
            service('oauth_server.config'),
        ])
        ->alias(OAuthAuthenticator::class, 'oauth_server.security.authenticator')
    ;
};
