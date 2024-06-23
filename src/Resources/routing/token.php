<?php

declare(strict_types=1);

use OAuth\Controller\TokenController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes
        ->add('oauth_server_token', '/oauth/v2/token')
        ->controller([TokenController::class, 'token'])
        ->methods([Request::METHOD_GET, Request::METHOD_HEAD])
    ;
};
