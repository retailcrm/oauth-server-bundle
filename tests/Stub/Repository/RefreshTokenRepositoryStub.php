<?php

declare(strict_types=1);

namespace OAuth\Tests\Stub\Repository;

use OAuth\Doctrine\Repository\RefreshTokenRepositoryInterface;
use OAuth\Model\ClientInterface;
use OAuth\Model\RefreshTokenInterface;
use OAuth\Tests\Stub\Entity\RefreshToken;

class RefreshTokenRepositoryStub implements RefreshTokenRepositoryInterface
{
    /** @var array<string, RefreshTokenInterface> */
    private array $tokens = [];

    public function findByToken(string $token): ?RefreshTokenInterface
    {
        return $this->tokens[$token] ?? null;
    }

    public function createRefreshToke(ClientInterface $client): RefreshTokenInterface
    {
        return new RefreshToken($client);
    }

    public function updateRefreshToke(RefreshTokenInterface $token): void
    {
        $this->tokens[$token->getToken()] = $token;
    }

    public function deleteRefreshToke(RefreshTokenInterface $token): void
    {
        unset($this->tokens[$token->getToken()]);
    }

    public function deleteTokenExpired(): int
    {
        return 0;
    }
}
