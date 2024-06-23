<?php

declare(strict_types=1);

namespace OAuth\Doctrine\Repository;

use OAuth\Model\ClientInterface;

interface ClientRepositoryInterface
{
    public function createClient(): ClientInterface;

    public function updateClient(ClientInterface $client): void;

    public function findByPublicId(string $publicId): ?ClientInterface;
}
