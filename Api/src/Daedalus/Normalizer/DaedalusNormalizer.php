<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\AlertEnum;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Status\Enum\StatusEnum;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DaedalusNormalizer implements ContextAwareNormalizerInterface
{
    private CycleServiceInterface $cycleService;
    private GameConfig $gameConfig;
    private TranslatorInterface $translator;

    public function __construct(
        CycleServiceInterface $cycleService,
        GameConfigServiceInterface $gameConfigService,
        TranslatorInterface $translator
    ) {
        $this->cycleService = $cycleService;
        $this->gameConfig = $gameConfigService->getConfig();
        $this->translator = $translator;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Daedalus;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Daedalus $daedalus */
        $daedalus = $object;

        return [
                'id' => $object->getId(),
                'cycle' => $object->getCycle(),
                'day' => $object->getDay(),
                'oxygen' => $object->getOxygen(),
                'fuel' => $object->getFuel(),
                'hull' => $object->getHull(),
                'shield' => $object->getShield(),
                'nextCycle' => $this->cycleService->getDateStartNextCycle($object)->format(\DateTime::ATOM),
                'cryogenizedPlayers' => $this->gameConfig->getCharactersConfig()->count() - $daedalus->getPlayers()->count(),
                'humanPlayerAlive' => $daedalus->getPlayers()->getHumanPlayer()->getPlayerAlive()->count(),
                'humanPlayerDead' => $daedalus->getPlayers()->getHumanPlayer()->getPlayerDead()->count(),
                'mushPlayerAlive' => $daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count(),
                'mushPlayerDead' => $daedalus->getPlayers()->getMushPlayer()->getPlayerDead()->count(),
            ];
    }

    public function minimap($daedalus): array
    {
        $minimap = [];
        foreach ($daedalus->getRoom() as $room) {
            $minimap[$room->getName()] = ['players' => $room->getPlayers()->count()];

            //@TODO add project fire detector, anomaly detector doors detectors and actopi protocol
        }

        return $minimap;
    }

    public function getAlerts(Daedalus $daedalus): array
    {
        $alerts = [];

        $fire = 0;
        $brokenDoors = 0;
        $brokenEquipments = 0;

        foreach ($daedalus->getRooms() as $room) {
            if ($room->getStatusByName(StatusEnum::FIRE)) {
                $fire = $fire + 1;
            }
            $brokenDoors = $brokenDoors + $room->getEquipment()
                ->filter(fn (GameEquipment $equipment) => $equipment instanceof Door && $equipment->isBroken())->count();
            $brokenEquipments = $brokenEquipments + $room->getEquipment()
                ->filter(fn (GameEquipment $equipment) => $equipment->isPureEquipment() && $equipment->isBroken())->count();
        }

        if ($fire !== 0) {
            $alert = $this->translateAlert(AlertEnum::NUMBER_FIRE, $fire);
            $alerts[] = $alert;
        }
        if ($brokenDoors !== 0) {
            $alert = $this->translateAlert(AlertEnum::BROKEN_DOORS, $brokenDoors);
            $alerts[] = $alert;
        }
        if ($brokenEquipments !== 0) {
            $alert = $this->translateAlert(AlertEnum::BROKEN_EQUIPMENTS, $brokenEquipments);
            $alerts[] = $alert;
        }

        if ($daedalus->getOxygen() < 8) {
            $alert = $this->translateAlert(AlertEnum::LOW_OXYGEN);
            $alerts[] = $alert;
        }
        if ($daedalus->getHull() <= 33) {
            $alert = $this->translateAlert(AlertEnum::LOW_HULL, $daedalus->getHull());
            $alerts[] = $alert;
        }

        return $alerts;
    }

    public function translateAlert(string $key, ?int $quantity = null): array
    {
        if ($quantity !== null) {
            if ($quantity > 1) {
                $plural = '.plural';
            } else {
                $plural = '.single';
            }
            $alert = [
                'key' => $key,
                'name' => $this->translator->trans($key . '.name' . $plural, ['quantity' => $quantity], 'alerts'),
                'description' => $this->translator->trans($key . '.description', [], 'alerts'),
            ];
        } else {
            $alert = [
                'key' => $key,
                'name' => $this->translator->trans($key . '.name', [], 'alerts'),
                'description' => $this->translator->trans($key . '.description', [], 'alerts'),
            ];
        }

        return $alert;
    }
}
