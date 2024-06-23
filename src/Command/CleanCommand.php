<?php

declare(strict_types=1);

namespace OAuth\Command;

use OAuth\Server\Storage\AccessTokenStorageInterface;
use OAuth\Server\Storage\AuthCodeStorageInterface;
use OAuth\Server\Storage\DeleteExpiredStorageInterface;
use OAuth\Server\Storage\RefreshTokenStorageInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'oauth-server:clean',
    description: 'Clean expired tokens.',
)]
class CleanCommand extends Command
{
    public function __construct(
        private readonly AccessTokenStorageInterface $accessTokenStorage,
        private readonly RefreshTokenStorageInterface $refreshTokenStorage,
        private readonly AuthCodeStorageInterface $authCodeStorage
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ([$this->accessTokenStorage, $this->refreshTokenStorage, $this->authCodeStorage] as $service) {
            if (!$service instanceof DeleteExpiredStorageInterface) {
                throw new \LogicException(sprintf('The service "%s" must implement "%s".', $service::class, DeleteExpiredStorageInterface::class));
            }

            $result = $service->deleteExpired();
            $output->writeln(sprintf('Removed <info>%d</info> items from <comment>%s</comment> storage.', $result, $service::class));
        }

        return Command::SUCCESS;
    }
}
