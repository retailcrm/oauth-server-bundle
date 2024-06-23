<?php

declare(strict_types=1);

use OAuth\Server\TokenGenerator\MtRandTokenGenerator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('oauth_server.token_generator.mt_rand', MtRandTokenGenerator::class)
        ->alias(MtRandTokenGenerator::class, 'oauth_server.token_generator.mt_rand')
    ;
};
