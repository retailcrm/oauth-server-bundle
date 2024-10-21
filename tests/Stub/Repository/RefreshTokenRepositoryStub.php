<?php

declare(strict_types=1);

namespace OAuth\Tests\Stub\Repository;

use Doctrine\ORM\EntityRepository;
use OAuth\Doctrine\Repository\RefreshTokenRepositoryInterface;
use OAuth\Model\ClientInterface;
use OAuth\Model\RefreshTokenInterface;
use OAuth\Tests\Stub\Entity\RefreshToken;

class RefreshTokenRepositoryStub extends EntityRepository implements RefreshTokenRepositoryInterface
{
    /** @var array<string, RefreshTokenInterface> */
    private array $tokens = [];

    public function findByToken(string $token): ?RefreshTokenInterface
    {
        return $this->tokens[$token] ?? null;
    }

    public function createRefreshToken(ClientInterface $client): RefreshTokenInterface
    {
        return new RefreshToken($client);
    }

    public function updateRefreshToken(RefreshTokenInterface $token): void
    {
        $this->tokens[$token->getToken()] = $token;
    }

    public function deleteRefreshToken(RefreshTokenInterface $token): void
    {
        unset($this->tokens[$token->getToken()]);
    }

    public function deleteTokenExpired(): int
    {
        return 0;
    }
}
