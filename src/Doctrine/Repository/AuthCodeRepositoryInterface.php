<?php

declare(strict_types=1);

namespace OAuth\Doctrine\Repository;

use OAuth\Model\AuthCodeInterface;
use OAuth\Model\ClientInterface;

interface AuthCodeRepositoryInterface extends DeleteExpiredInterface
{
    public function findByCode(string $code): ?AuthCodeInterface;

    public function createAuthCode(ClientInterface $client): AuthCodeInterface;

    public function updateAuthCode(AuthCodeInterface $authCode): void;

    public function deleteAuthCode(AuthCodeInterface $authCode): void;
}
