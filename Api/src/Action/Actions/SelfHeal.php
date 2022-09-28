<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Validator\AreMedicalSuppliesOnReach;
use Mush\Action\Validator\FullHealth;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * implement self heal action.
 * For 3 Action Points, this action gives back 3 health points to the player which uses it.
 *  - +1 health point if the Ultra-healing pommade research is active (@TODO)
 *  - +2 health point if the player has the Medic skill (@TODO).
 *
 * Also weakens / heals diseases
 *
 * More info: http://www.mushpedia.com/wiki/Medikit
 */
class SelfHeal extends AbstractAction
{
    public const BASE_HEAL = 3;

    protected string $name = ActionEnum::SELF_HEAL;

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter === null;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new FullHealth(['target' => FullHealth::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new AreMedicalSuppliesOnReach([
            'groups' => ['visibility'],
        ]));
    }

    protected function checkResult(): ActionResult
    {
        $healedQuantity = self::BASE_HEAL;
        $success = new Success();
        return $success->setQuantity($healedQuantity);
    }

    protected function applyEffect(ActionResult $result): void
    {
        $playerModifierEvent = new PlayerVariableEvent(
            $this->player,
            PlayerVariableEnum::HEALTH_POINT,
            $result->getQuantity(),
            $this->getActionName(),
            new \DateTime()
        );
        $playerModifierEvent->setVisibility(VisibilityEnum::HIDDEN);
        $this->eventService->dispatch($playerModifierEvent, AbstractQuantityEvent::CHANGE_VARIABLE);

        $healEvent = new ApplyEffectEvent(
            $this->player,
            $this->player,
            VisibilityEnum::PRIVATE,
            $this->getActionName(),
            new \DateTime()
        );
        $this->eventService->dispatch($healEvent, ApplyEffectEvent::HEAL);
    }
}
