<?php

declare(strict_types=1);

namespace OAuth\Server\Storage;

use OAuth\Model\AuthCodeInterface;
use OAuth\Model\ClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface AuthCodeStorageInterface extends DeleteExpiredStorageInterface
{
    public function getAuthCode(string $code): ?AuthCodeInterface;

    public function createAuthCode(string $code, ClientInterface $client, ?UserInterface $user, string $redirectUri, int $expires, ?string $scope = null): void;

    public function markAuthCodeAsUsed(string $code): void;
}
