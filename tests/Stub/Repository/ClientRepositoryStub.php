<?php

declare(strict_types=1);

namespace OAuth\Tests\Stub\Repository;

use OAuth\Doctrine\Repository\ClientRepositoryInterface;
use OAuth\Model\ClientInterface;
use OAuth\Tests\Stub\Entity\Client;
use OAuth\Utils\Random;

class ClientRepositoryStub implements ClientRepositoryInterface
{
    /** @var array<string, ClientInterface> */
    private array $tokens = [];

    public function createClient(): ClientInterface
    {
        $client = new Client(Random::generateToken());
        $client->setSecret(Random::generateToken());

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
