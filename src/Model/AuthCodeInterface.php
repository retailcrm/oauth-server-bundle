<?php

declare(strict_types=1);

namespace OAuth\Model;

interface AuthCodeInterface extends TokenInterface
{
    public function setRedirectUri(string $redirectUri): self;

    public function getRedirectUri(): string;
}
