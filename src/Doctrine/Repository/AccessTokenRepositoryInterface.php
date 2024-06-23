<?php

declare(strict_types=1);

namespace OAuth\Doctrine\Repository;

use OAuth\Model\AccessTokenInterface;
use OAuth\Model\ClientInterface;

interface AccessTokenRepositoryInterface extends DeleteExpiredInterface
{
    public function findByToken(string $token): ?AccessTokenInterface;

    public function createAccessToken(ClientInterface $client): AccessTokenInterface;

    public function updateAccessToken(AccessTokenInterface $token): void;
}
