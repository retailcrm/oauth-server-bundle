<?php

declare(strict_types=1);

namespace OAuth\Tests\Stub\Repository;

use OAuth\Doctrine\Repository\AuthCodeRepositoryInterface;
use OAuth\Model\AuthCodeInterface;
use OAuth\Model\ClientInterface;
use OAuth\Tests\Stub\Entity\AuthCode;

class AuthCodeRepositoryStub implements AuthCodeRepositoryInterface
{
    /** @var array<string, AuthCodeInterface> */
    private array $codes = [];

    public function findByCode(string $code): ?AuthCodeInterface
    {
        return $this->codes[$code] ?? null;
    }

    public function createAuthCode(ClientInterface $client): AuthCodeInterface
    {
        return new AuthCode($client);
    }

    public function updateAuthCode(AuthCodeInterface $authCode): void
    {
        $this->codes[$authCode->getToken()] = $authCode;
    }

    public function deleteAuthCode(AuthCodeInterface $authCode): void
    {
        unset($this->codes[$authCode->getToken()]);
    }

    public function deleteTokenExpired(): int
    {
        return 0;
    }
}
