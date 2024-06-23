<?php

declare(strict_types=1);

namespace OAuth\Command;

use OAuth\Server\Storage\ClientStorageInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'oauth-server:create-client',
    description: 'Creates a new client.',
)]
class CreateClientCommand extends Command
{
    public function __construct(
        private readonly ClientStorageInterface $clientStorage,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->addOption(
                'redirect-uri',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets redirect uri for client. Use this option multiple times to set multiple redirect URIs.',
                null
            )
            ->addOption(
                'grant-type',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Sets allowed grant type for client. Use this option multiple times to set multiple grant types..',
                null
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Client Credentials');

        $client = $this->clientStorage->createClient();

        $client->setRedirectUris($input->getOption('redirect-uri'));
        $client->setGrantTypes($input->getOption('grant-type'));

        $this->clientStorage->updateClient($client);

        $io->table(['Client ID', 'Client Secret'], [
            [$client->getPublicId(), $client->getSecret()],
        ]);

        return 0;
    }
}
