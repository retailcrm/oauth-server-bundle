<?php

declare(strict_types=1);

use OAuth\Server\BearerToken\ChainExtractor;
use OAuth\Server\BearerToken\FormExtractor;
use OAuth\Server\BearerToken\HeaderExtractor;
use OAuth\Server\BearerToken\QueryExtractor;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services
        ->set('oauth_server.bearer_token.form_extractor', FormExtractor::class)
        ->alias(FormExtractor::class, 'oauth_server.bearer_token.form_extractor')
    ;

    $services
        ->set('oauth_server.bearer_token.header_extractor', HeaderExtractor::class)
        ->alias(HeaderExtractor::class, 'oauth_server.bearer_token.header_extractor')
    ;

    $services
        ->set('oauth_server.bearer_token.query_extractor', QueryExtractor::class)
        ->alias(QueryExtractor::class, 'oauth_server.bearer_token.query_extractor')
    ;

    $services
        ->set('oauth_server.bearer_token.chain_extractor', ChainExtractor::class)
        ->args([
            [service(HeaderExtractor::class), service(FormExtractor::class), service(QueryExtractor::class)],
        ])
        ->alias(ChainExtractor::class, 'oauth_server.bearer_token.chain_extractor')
    ;
};
