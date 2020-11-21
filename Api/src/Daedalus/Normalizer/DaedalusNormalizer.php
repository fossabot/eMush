<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class DaedalusNormalizer implements ContextAwareNormalizerInterface
{
    private CycleServiceInterface $cycleService;
    private GameConfig $gameConfig;

    public function __construct(
        CycleServiceInterface $cycleService,
        GameConfigServiceInterface $gameConfigService
    ) {
        $this->cycleService = $cycleService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Daedalus;
    }

    /**
     * @param Daedalus $daedalus
     *
     * @return array
     */
    public function normalize($daedalus, string $format = null, array $context = [])
    {
        return [
                'id' => $daedalus->getId(),
                'cycle' => $daedalus->getCycle(),
                'day' => $daedalus->getDay(),
                'oxygen' => $daedalus->getOxygen(),
                'fuel' => $daedalus->getFuel(),
                'hull' => $daedalus->getHull(),
                'shield' => $daedalus->getShield(),
                'nextCycle' => $this->cycleService->getDateStartNextCycle($daedalus)->format(\DateTime::ATOM),
                'createdAt' => $daedalus->getCreatedAt(),
                'updatedAt' => $daedalus->getUpdatedAt(),
            ];
    }
}
