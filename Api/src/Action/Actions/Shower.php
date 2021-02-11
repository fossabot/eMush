<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Fail;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Shower extends AbstractAction
{
    protected string $name = ActionEnum::SHOWER;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private StatusServiceInterface $statusService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
        PlayerServiceInterface $playerService,
        GearToolServiceInterface $gearToolService,
        ActionModifierServiceInterface $actionModifierService
    ) {
        parent::__construct(
            $eventDispatcher,
            $gearToolService,
            $actionModifierService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
        $this->playerService = $playerService;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->gameEquipment = $equipment;
    }

    public function canExecute(): bool
    {
        return $this->player->canReachEquipment($this->gameEquipment) &&
               !$this->gameEquipment->isBroken() &&
               $this->gameEquipment->getEquipment()->hasAction(ActionEnum::SHOWER)
            ;
    }

    protected function applyEffects(): ActionResult
    {
        if ($dirty = $this->player->getStatusByName(PlayerStatusEnum::DIRTY)) {
            $this->player->removeStatus($dirty);
        }

        if ($this->player->isMush()) {
            $actionModifier = new Modifier();
            $actionModifier
                ->setDelta(-3)
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
            ;
            $playerEvent = new PlayerEvent($this->player);
            $playerEvent->setReason(EndCauseEnum::CLUMSINESS);
            $playerEvent->setModifier($actionModifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }

        $this->playerService->persist($this->player);

        //@Hack: Mush 'fails' the shower to get different log
        return $this->player->isMush() ? new Fail() : new Success();
    }
}
