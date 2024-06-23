<?php

declare(strict_types=1);

namespace OAuth\Controller;

use OAuth\Exception\OAuthServerException;
use OAuth\Server\Handler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenController
{
    public function __construct(private readonly Handler $handler)
    {
    }

    public function token(Request $request): Response
    {
        try {
            return $this->handler->grantAccessToken($request);
        } catch (OAuthServerException $exception) {
            return $exception->getHttpResponse();
        }
    }
}
