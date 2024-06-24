<?php

declare(strict_types=1);

namespace OAuth\Doctrine\Storage;

use Doctrine\ORM\EntityManagerInterface;
use OAuth\Doctrine\Repository\ClientRepositoryInterface;
use OAuth\Model\ClientInterface;
use OAuth\Server\Storage\ClientStorageInterface;

class ClientStorage implements ClientStorageInterface
{
    private ClientRepositoryInterface $repository;

    public function __construct(
        private readonly EntityManagerInterface $em,
        string $className
    ) {
        $repository = $this->em->getRepository($className);

        if (!$repository instanceof ClientRepositoryInterface) {
            throw new \InvalidArgumentException(sprintf('The repository must implement %s', ClientRepositoryInterface::class));
        }

        $this->repository = $repository;
    }

    public function createClient(string $publicId, ?string $secret): ClientInterface
    {
        return $this->repository->createClient($publicId, $secret);
    }

    public function updateClient(ClientInterface $client): void
    {
        $this->repository->updateClient($client);
    }

    public function getClient(string $pubicId): ?ClientInterface
    {
        return $this->repository->findByPublicId($pubicId);
    }
}
