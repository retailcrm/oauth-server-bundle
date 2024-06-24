<?php

declare(strict_types=1);

namespace OAuth\Server\GrantExtension;

use OAuth\Enum\ErrorCode;
use OAuth\Exception\OAuthServerException;
use OAuth\Model\ClientInterface;
use OAuth\Server\Config;
use Symfony\Component\HttpFoundation\Response;

class CustomGrantExtension implements GrantExtensionInterface
{
    /**
     * @param GrantExtensionInterface[] $extensions
     */
    public function __construct(private array $extensions = [])
    {
    }

    public function addExtension(string $grantType, GrantExtensionInterface $extension): void
    {
        $this->extensions[$grantType] = $extension;
    }

    public function getExtensions(): array
    {
        return $this->extensions;
    }

    public function checkGrantExtension(ClientInterface $client, Config $config, string $grantType, array $input): Grant
    {
        if (!str_starts_with($input['grant_type'], 'urn:') && !filter_var($input['grant_type'], FILTER_VALIDATE_URL)) {
            throw new OAuthServerException(
                Response::HTTP_BAD_REQUEST,
                ErrorCode::ERROR_INVALID_REQUEST,
                'Invalid grant_type parameter or parameter missing'
            );
        }

        $extension = $this->getExtensions()[$input['grant_type']] ?? null;
        if (!$extension instanceof GrantExtensionInterface) {
            throw new OAuthServerException(
                Response::HTTP_BAD_REQUEST,
                ErrorCode::ERROR_INVALID_REQUEST,
                'Invalid grant_type parameter or parameter missing'
            );
        }

        return $extension->checkGrantExtension($client, $config, $grantType, $input);
    }
}
