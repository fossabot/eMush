<?php

namespace Mush\Disease\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseCauseConfig;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\EquipmentStatusEnum;

class DiseaseCauseService implements DiseaseCauseServiceInterface
{
    private const HAZARDOUS_RATE = 50;
    private const DECOMPOSING_RATE = 90;

    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private RandomServiceInterface $randomService;
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;

    public function __construct(
        PlayerDiseaseServiceInterface $playerDiseaseService,
        RandomServiceInterface $randomService,
        ConsumableDiseaseServiceInterface $consumableDiseaseService,
    ) {
        $this->playerDiseaseService = $playerDiseaseService;
        $this->randomService = $randomService;
        $this->consumableDiseaseService = $consumableDiseaseService;
    }

    public function handleSpoiledFood(Player $player, GameEquipment $gameEquipment): void
    {
        if (($gameEquipment->hasStatus(EquipmentStatusEnum::HAZARDOUS)
                && $this->randomService->isSuccessful(self::HAZARDOUS_RATE))
            || ($gameEquipment->hasStatus(EquipmentStatusEnum::DECOMPOSING)
                && $this->randomService->isSuccessful(self::DECOMPOSING_RATE))
        ) {
            $this->handleDiseaseForCause(DiseaseCauseEnum::PERISHED_FOOD, $player);
        }
    }

    public function handleConsumable(Player $player, GameEquipment $gameEquipment): void
    {
        $consumableEffect = $this->consumableDiseaseService->findConsumableDiseases($gameEquipment->getName(), $player->getDaedalus());

        if ($consumableEffect !== null) {
            /** @var ConsumableDiseaseAttribute $disease */
            foreach ($consumableEffect->getDiseases() as $disease) {
                if ($this->randomService->isSuccessful($disease->getRate())) {
                    $this->playerDiseaseService->createDiseaseFromName(
                        $disease->getDisease(),
                        $player,
                        [DiseaseCauseEnum::CONSUMABLE_EFFECT],
                        $disease->getDelayMin(),
                        $disease->getDelayLength()
                    );
                }
            }

            /** @var ConsumableDiseaseAttribute $cure */
            foreach ($consumableEffect->getCures() as $cure) {
                if (($disease = $player->getMedicalConditionByName($cure->getDisease())) !== null
                    && $this->randomService->isSuccessful($cure->getRate())
                ) {
                    $this->playerDiseaseService->removePlayerDisease($disease, [DiseaseStatusEnum::DRUG_HEALED], new \DateTime(), VisibilityEnum::PRIVATE);
                }
            }
        }
    }

    public function findCauseConfigByDaedalus(string $causeName, Daedalus $daedalus): DiseaseCauseConfig
    {
        $causesConfigs = $daedalus->getGameConfig()->getDiseaseCauseConfig()->filter(fn (DiseaseCauseConfig $causeConfig) => $causeConfig->getCauseName() === $causeName);

        if ($causesConfigs->count() !== 1) {
            throw new \Exception("there should be exactly 1 diseaseCauseConfig for this cause ({$causeName}).");
        }

        return $causesConfigs->first();
    }

    public function handleDiseaseForCause(string $cause, Player $player, int $delayMin = null, int $delayLength = null): void
    {
        $diseasesProbaArray = $this->findCauseConfigByDaedalus($cause, $player->getDaedalus())->getDiseases();

        $playerDiseases = $player->getMedicalConditions()->toArray();
        $playerDiseasesNames = array_map(function (PlayerDisease $playerDisease) {
            return $playerDisease->getDiseaseConfig()->getDiseaseName();
        }, $playerDiseases);

        $diseasesProbaArray = $diseasesProbaArray->withdrawElements($playerDiseasesNames);

        if (count($diseasesProbaArray) === 0) {
            return;
        }

        $diseaseName = $this->randomService->getSingleRandomElementFromProbaCollection($diseasesProbaArray);

        if (!is_string($diseaseName)) {
            return;
        }

        $this->playerDiseaseService->createDiseaseFromName($diseaseName, $player, [$cause], $delayMin, $delayLength);
    }
}
