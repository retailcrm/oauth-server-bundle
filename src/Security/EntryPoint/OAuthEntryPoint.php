<?php

declare(strict_types=1);

namespace OAuth\Security\EntryPoint;

use OAuth\Exception\OAuthAuthenticateException;
use OAuth\Server\Config;
use OAuth\Server\Handler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class OAuthEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(private readonly Config $config)
    {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $exception = new OAuthAuthenticateException(
            Response::HTTP_UNAUTHORIZED,
            Handler::TOKEN_TYPE_BEARER,
            $this->config->getVariable(Config::CONFIG_WWW_REALM),
            'access_denied',
            'OAuth2 authentication required'
        );

        return $exception->getHttpResponse();
    }
}
