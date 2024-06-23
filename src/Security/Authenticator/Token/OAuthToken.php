<?php

declare(strict_types=1);

namespace OAuth\Security\Authenticator\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class OAuthToken extends AbstractToken
{
    protected string $token;

    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function getCredentials(): string
    {
        return $this->token;
    }
}
