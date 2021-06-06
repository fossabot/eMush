<?php

namespace Mush\Equipment\Listener;

use Mush\Action\Event\ConsumeEvent;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ConsumeSubscriber implements EventSubscriberInterface
{
    private EventDispatcherInterface $eventDispatcher;
    private EquipmentEffectServiceInterface $equipmentServiceEffect;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EquipmentEffectServiceInterface $equipmentServiceEffect
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->equipmentServiceEffect = $equipmentServiceEffect;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ConsumeEvent::CONSUME => 'onConsume',
        ];
    }

    public function onConsume(ConsumeEvent $consumeEvent)
    {
        $player = $consumeEvent->getPlayer();
        $ration = $consumeEvent->getGameItem();
        $rationType = $consumeEvent->getGameItem()->getEquipment()->getRationsMechanic();

        if (null === $rationType) {
            throw new \Exception('Cannot consume this equipment');
        }

        $consumableEffect = $this->equipmentServiceEffect->getConsumableEffect($rationType, $player->getDaedalus());

        if (!$player->isMush()) {
            $this->dispatchConsumableEffects($consumableEffect, $player);
        } else {
            $this->dispatchMushEffect($player);
        }

        // if no charges consume equipment
        $equipmentEvent = new EquipmentEvent($ration, VisibilityEnum::HIDDEN, new \DateTime());
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }

    protected function dispatchConsumableEffects(ConsumableEffect $consumableEffect, Player $player): void
    {
        if (($delta = $consumableEffect->getActionPoint()) !== null) {
            $playerModifierEvent = new PlayerModifierEvent($player, $delta, new \DateTime());
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::ACTION_POINT_MODIFIER);
        }
        if (($delta = $consumableEffect->getMovementPoint()) !== null) {
            $playerModifierEvent = new PlayerModifierEvent($player, $delta, new \DateTime());
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::MOVEMENT_POINT_MODIFIER);
        }
        if (($delta = $consumableEffect->getHealthPoint()) !== null) {
            $playerModifierEvent = new PlayerModifierEvent($player, $delta, new \DateTime());
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::HEALTH_POINT_MODIFIER);
        }
        if (($delta = $consumableEffect->getMoralPoint()) !== null) {
            $playerModifierEvent = new PlayerModifierEvent($player, $delta, new \DateTime());
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);
        }
        if (($delta = $consumableEffect->getSatiety()) !== null) {
            $playerModifierEvent = new PlayerModifierEvent($player, $delta, new \DateTime());
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        }
    }

    protected function dispatchMushEffect(Player $player): void
    {
        $playerModifierEvent = new PlayerModifierEvent($player, 4, new \DateTime());
        $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
    }
}
