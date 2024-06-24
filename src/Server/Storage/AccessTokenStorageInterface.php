<?php

declare(strict_types=1);

namespace OAuth\Server\Storage;

use OAuth\Model\AccessTokenInterface;
use OAuth\Model\ClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface AccessTokenStorageInterface extends DeleteExpiredStorageInterface
{
    public function getAccessToken(string $token): ?AccessTokenInterface;

    public function createAccessToken(string $oauthToken, ClientInterface $client, ?UserInterface $user, int $expires, ?string $scope = null): AccessTokenInterface;
}
