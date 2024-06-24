<?php

declare(strict_types=1);

namespace OAuth\Server\GrantExtension;

use OAuth\Model\ClientInterface;
use OAuth\Server\Config;

interface GrantExtensionInterface
{
    /**
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-1.4.5
     * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.2
     */
    public function checkGrantExtension(ClientInterface $client, Config $config, string $grantType, array $input): Grant;
}
