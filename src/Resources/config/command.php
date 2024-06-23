<?php

declare(strict_types=1);

use OAuth\Command\CleanCommand;
use OAuth\Command\CreateClientCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('oauth_server.command.clean', CleanCommand::class)
        ->args([
            service('oauth_server.doctrine_storage.access_token'),
            service('oauth_server.doctrine_storage.refresh_token'),
            service('oauth_server.doctrine_storage.auth_code'),
        ])
        ->alias(CleanCommand::class, 'oauth_server.command.clean')
    ;

    $services
        ->set('oauth_server.command.create_client', CreateClientCommand::class)
        ->args([
            service('oauth_server.doctrine_storage.client'),
        ])
        ->alias(CreateClientCommand::class, 'oauth_server.command.create_client')
    ;
};
