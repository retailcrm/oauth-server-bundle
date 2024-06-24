<?php

declare(strict_types=1);

namespace OAuth\Tests\Stub\Repository;

use OAuth\Doctrine\Repository\ClientRepositoryInterface;
use OAuth\Model\ClientInterface;
use OAuth\Tests\Stub\Entity\Client;

class ClientRepositoryStub implements ClientRepositoryInterface
{
    /** @var array<string, ClientInterface> */
    private array $tokens = [];

    public function createClient(string $publicId, ?string $secret): ClientInterface
    {
        $client = new Client($publicId);
        $client->setSecret($secret);

        $this->tokens[$client->getPublicId()] = $client;

        return $client;
    }

    public function updateClient(ClientInterface $client): void
    {
        $this->tokens[$client->getPublicId()] = $client;
    }

    public function findByPublicId(string $publicId): ?ClientInterface
    {
        return $this->tokens[$publicId] ?? null;
    }
}
