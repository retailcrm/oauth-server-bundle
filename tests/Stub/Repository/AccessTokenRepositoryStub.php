<?php

declare(strict_types=1);

namespace OAuth\Tests\Stub\Repository;

use OAuth\Doctrine\Repository\AccessTokenRepositoryInterface;
use OAuth\Model\AccessTokenInterface;
use OAuth\Model\ClientInterface;
use OAuth\Tests\Stub\Entity\AccessToken;

class AccessTokenRepositoryStub implements AccessTokenRepositoryInterface
{
    /** @var array<string, AccessTokenInterface> */
    private array $tokens = [];

    public function findByToken(string $token): ?AccessTokenInterface
    {
        return $this->tokens[$token] ?? null;
    }

    public function createAccessToken(ClientInterface $client): AccessTokenInterface
    {
        return new AccessToken(
            $client,
            'bar'
        );
    }

    public function updateAccessToken(AccessTokenInterface $token): void
    {
        $this->tokens[$token->getToken()] = $token;
    }

    public function deleteTokenExpired(): int
    {
        return 0;
    }
}
