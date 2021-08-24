<?php

namespace Mush\Status\Listener;

use Mush\Equipment\Event\EquipmentEvent;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EquipmentSubscriber implements EventSubscriberInterface
{
    private StatusServiceInterface $statusService;

    public function __construct(
        StatusServiceInterface $statusService,
    ) {
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EquipmentEvent::EQUIPMENT_FIXED => 'onEquipmentFixed',
            EquipmentEvent::EQUIPMENT_BROKEN => 'onEquipmentBroken',
            EquipmentEvent::EQUIPMENT_TRANSFORM => ['onEquipmentTransform' => 1000], // change the status before original equipment is destroyed
        ];
    }

    public function onEquipmentFixed(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        if (($brokenStatus = $equipment->getStatusByName(EquipmentStatusEnum::BROKEN)) === null) {
            throw new \LogicException('equipment should be broken to be fixed');
        }

        $this->statusService->delete($brokenStatus);
    }

    public function onEquipmentBroken(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        $brokenStatus = $this->statusService->createCoreStatus(EquipmentStatusEnum::BROKEN, $equipment);
        $this->statusService->persist($brokenStatus);
    }

    public function onEquipmentTransform(EquipmentEvent $event): void
    {
        $equipment = $event->getEquipment();

        if (($newEquipment = $event->getReplacementEquipment()) === null) {
            throw new \LogicException('Replacement equipment should be provided');
        }

        /** @var Status $status */
        foreach ($equipment->getStatuses() as $status) {
            $newEquipment->addStatus($status);
            $this->statusService->persist($status);
        }
    }
}
