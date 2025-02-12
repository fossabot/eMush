<?php

namespace Mush\Modifier\Listener;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\EventModifierConfig;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Service\ModifierCreationServiceInterface;
use Mush\Modifier\Service\ModifierListenerService\EquipmentModifierServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\StatusHolderInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Event\StatusEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class StatusSubscriber implements EventSubscriberInterface
{
    private EquipmentModifierServiceInterface $gearModifierService;
    private ModifierCreationServiceInterface $modifierCreationService;

    public function __construct(
        EquipmentModifierServiceInterface $gearModifierService,
        ModifierCreationServiceInterface $modifierCreationService,
    ) {
        $this->gearModifierService = $gearModifierService;
        $this->modifierCreationService = $modifierCreationService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StatusEvent::STATUS_APPLIED => 'onStatusApplied',
            StatusEvent::STATUS_REMOVED => 'onStatusRemoved',
        ];
    }

    public function onStatusApplied(StatusEvent $event): void
    {
        $statusConfig = $event->getStatusConfig();
        if ($statusConfig === null) {
            throw new \LogicException('statusConfig should be provided');
        }

        $statusHolder = $event->getStatusHolder();

        foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
            $modifierHolder = $this->getModifierHolderFromConfig($statusHolder, $modifierConfig);
            if ($modifierHolder === null || !($modifierConfig instanceof EventModifierConfig)) {
                return;
            }

            $this->modifierCreationService->createModifier(
                $modifierConfig,
                $modifierHolder,
                $event->getTags(),
                $event->getTime(),
            );
        }

        // handle broken gears
        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            if (!$statusHolder instanceof GameEquipment) {
                throw new UnexpectedTypeException($statusHolder, GameEquipment::class);
            }
            $this->gearModifierService->gearDestroyed($statusHolder, $event->getTags(), $event->getTime());
        }
    }

    public function onStatusRemoved(StatusEvent $event): void
    {
        $statusHolder = $event->getStatusHolder();

        $statusConfig = $event->getStatusConfig();
        if ($statusConfig === null) {
            throw new \LogicException('statusConfig should be provided');
        }

        // handle broken gears
        if ($event->getStatusName() === EquipmentStatusEnum::BROKEN) {
            if (!$statusHolder instanceof GameEquipment) {
                throw new UnexpectedTypeException($statusHolder, GameEquipment::class);
            }
            $this->gearModifierService->gearCreated($statusHolder, $event->getTags(), $event->getTime());
        }

        foreach ($statusConfig->getModifierConfigs() as $modifierConfig) {
            $modifierHolder = $this->getModifierHolderFromConfig($statusHolder, $modifierConfig);
            if ($modifierHolder === null || !($modifierConfig instanceof EventModifierConfig)) {
                return;
            }

            $this->modifierCreationService->deleteModifier($modifierConfig, $modifierHolder, $event->getTags(), $event->getTime());
        }
    }

    private function getModifierHolderFromConfig(StatusHolderInterface $statusHolder, AbstractModifierConfig $modifierConfig): ?ModifierHolderInterface
    {
        return match ($modifierConfig->getModifierRange()) {
            ModifierHolderClassEnum::DAEDALUS => $this->getDaedalus($statusHolder),
            ModifierHolderClassEnum::PLACE => $this->getPlace($statusHolder),
            ModifierHolderClassEnum::PLAYER, ModifierHolderClassEnum::TARGET_PLAYER => $this->getPlayer($statusHolder),
            ModifierHolderClassEnum::EQUIPMENT => $this->getEquipment($statusHolder),
            default => null,
        };
    }

    private function getDaedalus(StatusHolderInterface $statusHolder): Daedalus
    {
        return $statusHolder->getDaedalus();
    }

    private function getPlace(StatusHolderInterface $statusHolder): ?Place
    {
        if ($statusHolder instanceof Place) {
            return $statusHolder;
        } elseif ($statusHolder instanceof Player) {
            return $statusHolder->getPlace();
        } elseif ($statusHolder instanceof GameEquipment) {
            return $statusHolder->getPlace();
        }

        return null;
    }

    private function getPlayer(StatusHolderInterface $statusHolder): ?Player
    {
        if ($statusHolder instanceof Player) {
            return $statusHolder;
        } elseif ($statusHolder instanceof GameEquipment) {
            return $statusHolder->getPlayer();
        }

        return null;
    }

    private function getEquipment(StatusHolderInterface $statusHolder): ?GameEquipment
    {
        if ($statusHolder instanceof GameEquipment) {
            return $statusHolder;
        }

        return null;
    }
}
