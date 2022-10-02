<?php

namespace Mush\Game\Command;

use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'mush:create-daedalus',
    description: 'Create a new Daedalus if none available.',
    hidden: false
)]
class CreateDaedalusCommand extends Command
{
    private DaedalusServiceInterface $service;
    private GameConfigServiceInterface $gameConfigService;

    public function __construct(DaedalusServiceInterface $service, GameConfigServiceInterface $gameConfigService)
    {
        parent::__construct();

        $this->service = $service;
        $this->gameConfigService = $gameConfigService;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->service->existAvailableDaedalus()) {
            $output->writeln('Creating Daedalus...');

            $name = Uuid::v4()->toRfc4122();
            $config = $this->gameConfigService->getConfig();
            $this->service->createDaedalus($config, $name);
            $output->writeln("Daedalus {$name} created.");

            return Command::SUCCESS;
        } else {
            $output->writeln('Their is an available Daedalus.');

            return Command::FAILURE;
        }
    }
}
