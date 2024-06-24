<?php

declare(strict_types=1);

namespace OAuth\Server\Storage;

use OAuth\Model\ClientInterface;

interface ClientStorageInterface
{
    public function createClient(string $publicId, ?string $secret): ClientInterface;

    public function updateClient(ClientInterface $client): void;

    public function getClient(string $pubicId): ?ClientInterface;
}
