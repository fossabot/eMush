<?php

namespace Mush\RoomLog\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\PlantLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private RoomLogServiceInterface $roomLogService;

    private const CREATION_LOG_MAP = [
        EventEnum::PLANT_PRODUCTION => PlantLogEnum::PLANT_NEW_FRUIT,
        ActionEnum::BUILD => ActionLogEnum::BUILD_SUCCESS,
        ActionEnum::TRANSPLANT => ActionLogEnum::TRANSPLANT_SUCCESS,
        ActionEnum::OPEN => ActionLogEnum::OPEN_SUCCESS,
    ];

    private const DESTRUCTION_LOG_MAP = [
        EventEnum::FIRE => LogEnum::EQUIPMENT_DESTROYED,
        PlantLogEnum::PLANT_DEATH => PlantLogEnum::PLANT_DEATH,
        EndCauseEnum::ASPHYXIA => LogEnum::OXY_LOW_USE_CAPSULE,
    ];

    private const MOVE_EQUIPMENT_LOG_MAP = [
        ActionEnum::COLLECT_SCRAP => LogEnum::SCRAP_COLLECTED,
    ];

    public function __construct(RoomLogServiceInterface $roomLogService)
    {
        $this->roomLogService = $roomLogService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::CHANGE_HOLDER => [
                ['onEquipmentChangeHolder'],
            ],
            EquipmentEvent::EQUIPMENT_CREATED => [
                ['onEquipmentCreated', 100],
            ],
            EquipmentEvent::EQUIPMENT_DESTROYED => [
                ['onEquipmentDestroyed'],
            ],
            EquipmentEvent::INVENTORY_OVERFLOW => [
                ['onInventoryOverflow'],
            ],
        ];
    }

    public function onEquipmentChangeHolder(EquipmentEvent $event): void
    {
        $logKey = $event->mapLog(self::MOVE_EQUIPMENT_LOG_MAP);

        if ($logKey !== null) {
            $this->createEventLog($logKey, $event, $event->getVisibility());
        }
    }

    public function onEquipmentCreated(EquipmentEvent $event): void
    {
        $logKey = $event->mapLog(self::CREATION_LOG_MAP);
        if ($logKey !== null) {
            $this->createEventLog($logKey, $event, $event->getVisibility());
        }
    }

    public function onEquipmentDestroyed(EquipmentEvent $event): void
    {
        $logKey = $event->mapLog(self::DESTRUCTION_LOG_MAP);

        if ($logKey !== null) {
            $this->createEventLog($logKey, $event, $event->getVisibility());
        }
    }

    public function onInventoryOverflow(EquipmentEvent $event): void
    {
        /** @var Player $holder */
        $holder = $event->getGameEquipment()->getHolder();

        $characterConfig = $holder->getPlayerInfo()->getCharacterConfig();
        $equipment = $event->getGameEquipment();

        if (
            $equipment instanceof GameItem
            && $holder->getEquipments()->count() > $characterConfig->getMaxItemInInventory()
        ) {
            $this->createEventLog(LogEnum::OBJECT_FELL, $event, VisibilityEnum::PUBLIC);
        }
    }

    private function createEventLog(string $logKey, EquipmentEvent $event, string $visibility): void
    {
        /* @var Player|null $player */
        if ($event instanceof InteractWithEquipmentEvent) {
            $player = $event->getAuthor();
        } elseif ($event->isCreated()) {
            $holder = $event->getGameEquipment()->getHolder();
            if ($holder instanceof Player) {
                $player = $holder;
            } else {
                $player = null;
            }
        } else {
            $player = null;
        }

        /** @var Place $logPlace */
        $logPlace = $event instanceof MoveEquipmentEvent ? $event->getNewHolder() : $event->getPlace();
        $this->roomLogService->createLog(
            $logKey,
            $logPlace,
            $visibility,
            'event_log',
            $player,
            $event->getLogParameters(),
            $event->getTime()
        );
    }
}
