<?php

namespace Mush\Modifier\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Service\EventModifierServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\EquipmentModifierServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\PlayerModifierServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private EquipmentModifierServiceInterface $equipmentModifierService;
    private EventModifierServiceInterface $modifierService;
    private PlayerModifierServiceInterface $playerModifierService;

    public function __construct(
        EquipmentModifierServiceInterface $equipmentModifierService,
        EventModifierServiceInterface $modifierService,
        PlayerModifierServiceInterface $playerModifierService
    ) {
        $this->equipmentModifierService = $equipmentModifierService;
        $this->modifierService = $modifierService;
        $this->playerModifierService = $playerModifierService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => 'onPreAction',
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPostAction(ActionEvent $event): void
    {
        $actionResult = $event->getActionResult();
        $player = $event->getPlayer();

        if ($actionResult === null) {
            return;
        }
        $actionName = $event->getAction()->getActionName();

        $target = $event->getActionParameter();

        // handle modifiers with charges
        $this->modifierService->applyActionModifiers($event->getAction(), $player, $target);

        switch ($actionName) {
            // handle gear modifiers when taken or dropped
            case ActionEnum::TAKE:
                if (!$target instanceof GameEquipment) {
                    throw new \LogicException('a game equipment should be given');
                }

                $this->equipmentModifierService->takeEquipment($target, $player, $event->getTags(), $event->getTime());

                return;
            case ActionEnum::DROP:
                if (!$target instanceof GameEquipment) {
                    throw new \LogicException('a game equipment should be given');
                }

                $this->equipmentModifierService->dropEquipment($target, $player, $event->getTags(), $event->getTime());

                return;

                // handle movement of a player
            case ActionEnum::MOVE:
                $this->playerModifierService->playerEnterRoom($player, $event->getTags(), $event->getTime());

                foreach ($player->getEquipments() as $equipment) {
                    $this->equipmentModifierService->equipmentEnterRoom($equipment, $player->getPlace(), $event->getTags(), $event->getTime());
                }
        }
    }

    public function onPreAction(ActionEvent $event): void
    {
        $player = $event->getPlayer();
        $actionName = $event->getAction()->getActionName();

        switch ($actionName) {
            case ActionEnum::MOVE:
                // handle movement of a player
                $this->playerModifierService->playerLeaveRoom($player, $event->getTags(), $event->getTime());

                foreach ($player->getEquipments() as $equipment) {
                    $this->equipmentModifierService->equipmentLeaveRoom($equipment, $player->getPlace(), $event->getTags(), $event->getTime());
                }

                return;
        }
    }
}
