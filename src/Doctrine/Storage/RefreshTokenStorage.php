<?php

declare(strict_types=1);

namespace OAuth\Doctrine\Storage;

use Doctrine\ORM\EntityManagerInterface;
use OAuth\Doctrine\Repository\RefreshTokenRepositoryInterface;
use OAuth\Model\ClientInterface;
use OAuth\Model\RefreshTokenInterface;
use OAuth\Server\Storage\RefreshTokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RefreshTokenStorage implements RefreshTokenStorageInterface
{
    private RefreshTokenRepositoryInterface $repository;

    public function __construct(
        private readonly EntityManagerInterface $em,
        string $className
    ) {
        $repository = $this->em->getRepository($className);

        if (!$repository instanceof RefreshTokenRepositoryInterface) {
            throw new \InvalidArgumentException(sprintf('The repository must implement %s', RefreshTokenRepositoryInterface::class));
        }

        $this->repository = $repository;
    }

    public function getRefreshToken(string $refreshToken): ?RefreshTokenInterface
    {
        return $this->repository->findByToken($refreshToken);
    }

    public function createRefreshToken(string $refreshToken, ClientInterface $client, ?UserInterface $user, int $expires, ?string $scope = null): RefreshTokenInterface
    {
        $token = $this->repository->createRefreshToke($client);
        $token
            ->setToken($refreshToken)
            ->setUser($user)
            ->setExpiresAt($expires)
            ->setScope($scope)
        ;

        $this->repository->updateRefreshToke($token);

        return $token;
    }

    public function unsetRefreshToken(string $refreshToken): void
    {
        $token = $this->repository->findByToken($refreshToken);

        if (null !== $token) {
            $this->repository->deleteRefreshToke($token);
        }
    }

    public function deleteExpired(): int
    {
        return $this->repository->deleteTokenExpired();
    }
}
