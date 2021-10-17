<?php

namespace Mush\Modifier\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\Action;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\GameEquipment;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

class ModifierService implements ModifierServiceInterface
{
    private const ATTEMPT_INCREASE = 1.25;
    private EntityManagerInterface $entityManager;
    private StatusServiceInterface $statusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        StatusServiceInterface $statusService
    ) {
        $this->entityManager = $entityManager;
        $this->statusService = $statusService;
    }

    public function persist(Modifier $modifier): Modifier
    {
        $this->entityManager->persist($modifier);
        $this->entityManager->flush();

        return $modifier;
    }

    public function delete(Modifier $modifier): void
    {
        $this->entityManager->remove($modifier);
        $this->entityManager->flush();
    }

    public function createModifier(
        ModifierConfig $modifierConfig,
        Daedalus $daedalus,
        ?Place $place,
        ?Player $player,
        ?GameEquipment $gameEquipment,
        ?ChargeStatus $chargeStatus
    ): void {
        switch ($modifierConfig->getReach()) {
            case ModifierReachEnum::DAEDALUS:
                $holder = $daedalus;
                break;

            case ModifierReachEnum::PLACE:
                $holder = $place;
                break;

            case ModifierReachEnum::PLAYER:
            case ModifierReachEnum::TARGET_PLAYER:
                $holder = $player;
                break;

            case ModifierReachEnum::EQUIPMENT:
                $holder = $gameEquipment;
                break;

            default:
                throw new \LogicException('this reach is not handled');
        }

        if ($holder === null) {
            return;
        }

        $modifier = new Modifier($holder, $modifierConfig);

        if ($chargeStatus) {
            $modifier->setCharge($chargeStatus);
        }

        $this->persist($modifier);
    }

    public function deleteModifier(
        ModifierConfig $modifierConfig,
        Daedalus $daedalus,
        ?Place $place,
        ?Player $player,
        ?GameEquipment $gameEquipment
    ): void {
        switch ($modifierConfig->getReach()) {
            case ModifierReachEnum::DAEDALUS:
                $modifier = $daedalus->getModifiers()->getModifierFromConfig($modifierConfig);
                $this->delete($modifier);

                return;

            case ModifierReachEnum::PLACE:
                if ($place !== null) {
                    $modifier = $place->getModifiers()->getModifierFromConfig($modifierConfig);
                    $this->delete($modifier);
                }

                return;

            case ModifierReachEnum::PLAYER:
            case ModifierReachEnum::TARGET_PLAYER:
                if ($player !== null) {
                    $modifier = $player->getModifiers()->getModifierFromConfig($modifierConfig);
                    $this->delete($modifier);
                }

                return;
            case ModifierReachEnum::EQUIPMENT:
                if ($gameEquipment !== null) {
                    $modifier = $gameEquipment->getModifiers()->getModifierFromConfig($modifierConfig);
                    $this->delete($modifier);
                }

                return;
        }
    }

    private function getModifiedValue(ModifierCollection $modifierCollection, ?float $initValue): int
    {
        if ($initValue === null) {
            return 0;
        }

        $multiplicativeDelta = 1;
        $additiveDelta = 0;

        /** @var Modifier $modifier */
        foreach ($modifierCollection as $modifier) {
            $chargeStatus = $modifier->getCharge();
            if (
                $chargeStatus === null ||
                $chargeStatus->getCharge() !== 0
            ) {
                if ($modifier->getModifierConfig()->getMode() === ModifierModeEnum::SET_VALUE) {
                    return intval($modifier->getModifierConfig()->getDelta());
                } elseif ($modifier->getModifierConfig()->getMode() === ModifierModeEnum::ADDITIVE) {
                    $additiveDelta += $modifier->getModifierConfig()->getDelta();
                } elseif ($modifier->getModifierConfig()->getMode() === ModifierModeEnum::MULTIPLICATIVE) {
                    $multiplicativeDelta *= $modifier->getModifierConfig()->getDelta();
                } else {
                    throw new \LogicException('this modifier mode is not handled');
                }
            }
        }

        return intval($initValue * $multiplicativeDelta + $additiveDelta);
    }

    private function getActionModifiers(Action $action, Player $player, ?LogParameterInterface $parameter): ModifierCollection
    {
        $modifiers = new ModifierCollection();

        $scopes = array_merge([$action->getName()], $action->getTypes());

        $modifiers = $modifiers
            ->addModifiers($player->getModifiers()->getScopedModifiers($scopes))
            ->addModifiers($player->getPlace()->getModifiers()->getScopedModifiers($scopes))
            ->addModifiers($player->getDaedalus()->getModifiers()->getScopedModifiers($scopes))
        ;

        if ($parameter instanceof Player) {
            $modifiers = $modifiers->addModifiers($parameter->getModifiers()->getScopedModifiers($scopes));
        } elseif ($parameter instanceof GameEquipment) {
            $modifiers = $modifiers->addModifiers($parameter->getModifiers()->getScopedModifiers($scopes));
        }

        return $modifiers;
    }

    public function getActionModifiedValue(Action $action, Player $player, string $target, ?LogParameterInterface $parameter, ?int $attemptNumber = null): int
    {
        $modifiers = $this->getActionModifiers($action, $player, $parameter);

        switch ($target) {
            case ModifierTargetEnum::ACTION_POINT:
                return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $action->getActionCost()->getActionPointCost());
            case ModifierTargetEnum::MOVEMENT_POINT:
                return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $action->getActionCost()->getMovementPointCost());
            case ModifierTargetEnum::MORAL_POINT:
                return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $action->getActionCost()->getMoralPointCost());
            case ModifierTargetEnum::PERCENTAGE:
                if ($attemptNumber === null) {
                    throw new InvalidTypeException('number of attempt should be provided');
                }

                $initialValue = $action->getSuccessRate() * (self::ATTEMPT_INCREASE) ** $attemptNumber;

                return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $initialValue);
        }

        throw new \LogicException('This target is not handled');
    }

    public function consumeActionCharges(Action $action, Player $player, ?LogParameterInterface $parameter): void
    {
        $modifiers = $this->getActionModifiers($action, $player, $parameter);

        foreach ($modifiers as $modifier) {
            if (($charge = $modifier->getCharge()) !== null) {
                $this->statusService->updateCharge($charge, -1);
            }
        }
    }

    public function getEventModifiedValue(Player $player, array $scopes, string $target, int $initValue): int
    {
        $modifiers = new ModifierCollection();
        $modifiers = $modifiers
            ->addModifiers($player->getModifiers()->getScopedModifiers($scopes)->getTargetedModifiers($target))
            ->addModifiers($player->getPlace()->getModifiers()->getScopedModifiers($scopes)->getTargetedModifiers($target))
            ->addModifiers($player->getDaedalus()->getModifiers()->getScopedModifiers($scopes)->getTargetedModifiers($target))
        ;

        $this->consumeEventCharges($modifiers);

        return $this->getModifiedValue($modifiers, $initValue);
    }

    private function consumeEventCharges(Collection $modifiers): void
    {
        foreach ($modifiers as $modifier) {
            if (($charge = $modifier->getCharge()) !== null) {
                $this->statusService->updateCharge($charge, -1);
            }
        }
    }
}
