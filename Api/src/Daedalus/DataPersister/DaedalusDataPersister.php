<?php

namespace Mush\Daedalus\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Service\DaedalusServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;

class DaedalusDataPersister implements ContextAwareDataPersisterInterface
{
    private DaedalusServiceInterface $daedalusService;
    private GameConfigServiceInterface $gameConfigService;

    public function __construct(DaedalusServiceInterface $daedalusService, GameConfigServiceInterface $gameConfigService)
    {
        $this->daedalusService = $daedalusService;
        $this->gameConfigService = $gameConfigService;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Daedalus;
    }

    public function persist($data, array $context = [])
    {
        $config = $this->gameConfigService->getConfigByName('default');

        return $this->daedalusService->createDaedalus($config, $data->getName());
    }

    public function remove($data, array $context = [])
    {
        // TODO: Implement remove() method.
    }
}
