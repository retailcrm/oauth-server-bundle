<?php

declare(strict_types=1);

namespace OAuth\Doctrine\Storage;

use Doctrine\ORM\EntityManagerInterface;
use OAuth\Doctrine\Repository\AuthCodeRepositoryInterface;
use OAuth\Model\AuthCodeInterface;
use OAuth\Model\ClientInterface;
use OAuth\Server\Storage\AuthCodeStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthCodeStorage implements AuthCodeStorageInterface
{
    private AuthCodeRepositoryInterface $repository;

    /**
     * @param class-string $className
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
        string $className,
    ) {
        /** @var class-string $className */
        $repository = $this->em->getRepository($className);

        if (!$repository instanceof AuthCodeRepositoryInterface) {
            throw new \InvalidArgumentException(sprintf('The repository must implement %s', AuthCodeRepositoryInterface::class));
        }

        $this->repository = $repository;
    }

    public function getAuthCode(string $code): ?AuthCodeInterface
    {
        return $this->repository->findByCode($code);
    }

    public function createAuthCode(string $code, ClientInterface $client, ?UserInterface $user, string $redirectUri, int $expires, ?string $scope = null): AuthCodeInterface
    {
        $authCode = $this->repository->createAuthCode($client);

        $authCode
            ->setRedirectUri($redirectUri)
            ->setUser($user)
            ->setToken($code)
            ->setClient($client)
            ->setExpiresAt($expires)
            ->setScope($scope)
        ;

        $this->repository->updateAuthCode($authCode);

        return $authCode;
    }

    public function markAuthCodeAsUsed(string $code): void
    {
        $authCode = $this->repository->findByCode($code);

        if (null !== $authCode) {
            $this->repository->deleteAuthCode($authCode);
        }
    }

    public function deleteExpired(): int
    {
        return $this->repository->deleteTokenExpired();
    }
}
