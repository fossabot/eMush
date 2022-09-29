<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Validator\Fuel;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\RoomLog\Entity\LogParameterInterface;

abstract class InsertAction extends AbstractAction
{
    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof Item;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var Item $toInsert */
        $toInsert = $this->getParameter();
        $time = new \DateTime();

        // Delete the fuel
        $equipmentEvent = new InteractWithEquipmentEvent(
            $toInsert,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            $time
        );
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        // Add to container
        $daedalusEvent = new DaedalusModifierEvent(
            $this->player->getDaedalus(),
            $this->getDaedalusVariable(),
            1,
            $this->getActionName(),
            $time
        );
        $this->eventDispatcher->dispatch($daedalusEvent, AbstractQuantityEvent::CHANGE_VARIABLE);
    }

    abstract protected function getDaedalusVariable(): string;
}
