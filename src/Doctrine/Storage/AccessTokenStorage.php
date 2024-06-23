<?php

declare(strict_types=1);

namespace OAuth\Doctrine\Storage;

use Doctrine\ORM\EntityManagerInterface;
use OAuth\Doctrine\Repository\AccessTokenRepositoryInterface;
use OAuth\Model\AccessTokenInterface;
use OAuth\Model\ClientInterface;
use OAuth\Server\Storage\AccessTokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessTokenStorage implements AccessTokenStorageInterface
{
    private AccessTokenRepositoryInterface $repository;

    public function __construct(
        private readonly EntityManagerInterface $em,
        string $className
    ) {
        $repository = $this->em->getRepository($className);

        if (!$repository instanceof AccessTokenRepositoryInterface) {
            throw new \InvalidArgumentException(sprintf('The repository must implement %s', AccessTokenRepositoryInterface::class));
        }

        $this->repository = $repository;
    }

    public function getAccessToken(string $token): ?AccessTokenInterface
    {
        return $this->repository->findByToken($token);
    }

    public function createAccessToken(string $oauthToken, ClientInterface $client, ?UserInterface $user, int $expires, ?string $scope = null): void
    {
        $token = $this->repository->createAccessToken($client);

        $token
            ->setToken($oauthToken)
            ->setUser($user)
            ->setExpiresAt($expires)
            ->setScope($scope)
        ;

        $this->repository->updateAccessToken($token);
    }

    public function deleteExpired(): int
    {
        return $this->repository->deleteTokenExpired();
    }
}
