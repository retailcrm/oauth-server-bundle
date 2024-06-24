<?php

declare(strict_types=1);

namespace OAuth\Doctrine\Repository;

use OAuth\Model\ClientInterface;
use OAuth\Model\RefreshTokenInterface;

interface RefreshTokenRepositoryInterface extends DeleteExpiredInterface
{
    public function findByToken(string $token): ?RefreshTokenInterface;

    public function createRefreshToken(ClientInterface $client): RefreshTokenInterface;

    public function updateRefreshToken(RefreshTokenInterface $token): void;

    public function deleteRefreshToken(RefreshTokenInterface $token): void;
}
