<?php

namespace Mush\Action\Service;

use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\Mechanics\Gear;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierScopeEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ActionService implements ActionServiceInterface
{
    const ACTION_INJURY_MODIFIER = -2;

    private EventDispatcherInterface $eventDispatcher;
    private RandomServiceInterface $randomService;
    private StatusServiceInterface $statusService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RandomServiceInterface $randomService,
        StatusServiceInterface $statusService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->randomService = $randomService;
        $this->statusService = $statusService;
        $this->roomLogService = $roomLogService;
    }

    public function handleActionSideEffect(Action $action, Player $player, ?\DateTime $date = null): Player
    {
        $dirtyRate = $action->getDirtyRate();
        $isSuperDirty = $dirtyRate > 100;
        if (!$player->hasStatus(PlayerStatusEnum::DIRTY) &&
            $dirtyRate > 0 &&
            ($percent = $this->randomService->randomPercent()) <= $dirtyRate
        ) {
            $gears = $player->getApplicableGears(
                [ModifierScopeEnum::EVENT_DIRTY],
                [ReachEnum::INVENTORY],
                ModifierTargetEnum::PERCENTAGE
            );

            /** @var Gear $gear */
            foreach ($gears as $gear) {
                $dirtyRate += $gear->getModifier()->getDelta();
            }

            if (!$isSuperDirty && $percent >= $dirtyRate) {
                $this->roomLogService->createPlayerLog(
                    LogEnum::SOIL_PREVENTED,
                    $player->getRoom(),
                    $player,
                    VisibilityEnum::PRIVATE,
                    $date
                );
            } else {
                $this->statusService->createCoreStatus(PlayerStatusEnum::DIRTY, $player);

                $this->roomLogService->createPlayerLog(
                    LogEnum::SOILED,
                    $player->getRoom(),
                    $player,
                    VisibilityEnum::PRIVATE,
                    $date
                );
            }
        }

        $injuryRate = $action->getInjuryRate();
        if ($injuryRate > 0 && $this->randomService->isSuccessfull($injuryRate)) {
            $this->roomLogService->createPlayerLog(
                LogEnum::CLUMSINESS,
                $player->getRoom(),
                $player,
                VisibilityEnum::PRIVATE,
                $date
            );
            $this->dispatchPlayerInjuryEvent($player, $date);
        }

        return $player;
    }

    private function dispatchPlayerInjuryEvent(Player $player, ?\DateTime $dateTime = null): void
    {
        $gears = $player->getApplicableGears(
            [ModifierScopeEnum::EVENT_CLUMSINESS],
            [ReachEnum::INVENTORY],
            ModifierTargetEnum::HEALTH_POINT
        );

        $defaultDelta = self::ACTION_INJURY_MODIFIER;
        /** @var Gear $gear */
        foreach ($gears as $gear) {
            $defaultDelta -= $gear->getModifier()->getDelta();
        }

        $modifier = new Modifier();
        $modifier
            ->setDelta($defaultDelta)
            ->setTarget(ModifierTargetEnum::HEALTH_POINT)
        ;

        $playerEvent = new PlayerEvent($player, $dateTime);
        $playerEvent->setModifier($modifier);
        $playerEvent->setReason(EndCauseEnum::CLUMSINESS);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
    }
}
