<?php

declare(strict_types=1);

namespace OAuth\Model;

abstract class Client implements ClientInterface
{
    public function checkGrantType(string $grantType): bool
    {
        return in_array($grantType, $this->getGrantTypes(), true);
    }

    public function checkSecret(?string $secret): bool
    {
        return null === $this->getSecret() || $secret === $this->getSecret();
    }
}
