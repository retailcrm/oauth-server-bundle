<?php

declare(strict_types=1);

namespace OAuth\Server\GrantExtension;

use OAuth\Enum\ErrorCode;
use OAuth\Exception\OAuthServerException;
use OAuth\Model\ClientInterface;
use OAuth\Server\Config;
use OAuth\Server\Storage\RefreshTokenStorageInterface;
use Symfony\Component\HttpFoundation\Response;

class RefreshTokenGrantExtension implements GrantExtensionInterface
{
    public function __construct(private readonly RefreshTokenStorageInterface $storage)
    {
    }

    public function checkGrantExtension(ClientInterface $client, Config $config, string $grantType, array $input, array $headers): Grant
    {
        if (!$input['refresh_token']) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_REQUEST, 'No "refresh_token" parameter found');
        }

        $token = $this->storage->getRefreshToken($input['refresh_token']);

        if (null === $token || $client->getPublicId() !== $token->getClient()->getPublicId()) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_GRANT, 'Invalid refresh token');
        }

        if ($token->hasExpired()) {
            throw new OAuthServerException(Response::HTTP_BAD_REQUEST, ErrorCode::ERROR_INVALID_GRANT, 'Refresh token has expired');
        }

        $this->storage->unsetRefreshToken($token->getToken());

        return new Grant($token->getUser(), $token->getScope());
    }
}
