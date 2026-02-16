<?php
declare(strict_types=1);

namespace App\Infrastructure\Azure\Blob\Command;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Response;

#[AsCommand(
    name: 'azure:blob:init',
    description: 'Initializes Azure Blob container if it does not exist',
)]
final class InitBlobContainerCommand extends Command
{
    public function __construct(
        private readonly BlobRestProxy $client,
        private readonly string $container,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->client->getContainerProperties($this->container);
            $output->writeln(sprintf('<info>OK</info> Container exists: %s', $this->container));
            return Command::SUCCESS;
        } catch (ServiceException $e) {
            if ($e->getCode() !== Response::HTTP_NOT_FOUND) {
                $output->writeln('<error>'.$e->getMessage().'</error>');
                return Command::FAILURE;
            }
        }

        $this->client->createContainer($this->container);
        $output->writeln(sprintf('<info>CREATED</info> Container: %s', $this->container));

        return Command::SUCCESS;
    }
}
