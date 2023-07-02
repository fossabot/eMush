<?php

declare(strict_types=1);

namespace Mush\Action\Listener;

use Mush\Action\ActionResult\Fail;
use Mush\Action\Actions\GetUp;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Action\Service\ActionSideEffectsServiceInterface;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Equipment\Entity\EquipmentMechanic as Mechanic;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ActionSubscriber implements EventSubscriberInterface
{
    private ActionSideEffectsServiceInterface $actionSideEffectsService;
    private EventServiceInterface $eventService;
    private GetUp $getUpAction;
    private GearToolServiceInterface $gearToolService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private StatusServiceInterface $statusService;

    public function __construct(
        ActionSideEffectsServiceInterface $actionSideEffectsService,
        EventServiceInterface $eventService,
        GetUp $getUp,
        GearToolServiceInterface $gearToolService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        StatusServiceInterface $statusService
    ) {
        $this->actionSideEffectsService = $actionSideEffectsService;
        $this->eventService = $eventService;
        $this->getUpAction = $getUp;
        $this->gearToolService = $gearToolService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->statusService = $statusService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::PRE_ACTION => ['onPreAction', 1],
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPreAction(ActionEvent $event): void
    {
        $action = $event->getAction();
        $player = $event->getAuthor();

        if ($action->getActionName() !== $this->getUpAction->getActionName() &&
            $lyingDownStatus = $player->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $getUpAction = $player->getPlayerInfo()->getCharacterConfig()->getActionByName(ActionEnum::GET_UP);

            if ($getUpAction === null) {
                throw new \LogicException('character do not have get up action');
            }

            $this->getUpAction->loadParameters($getUpAction, $player);
            $this->getUpAction->execute();
        }
    }

    public function onPostAction(ActionEvent $event): void
    {
        $action = $event->getAction();
        $player = $event->getAuthor();
        $actionParameter = $event->getActionParameter();

        $this->actionSideEffectsService->handleActionSideEffect($action, $player, $actionParameter);
        $this->gearToolService->applyChargeCost($player, $action->getActionName(), $action->getTypes());
        $player->getDaedalus()->addDailyActionPointsSpent($action->getActionCost());

        if ($actionParameter instanceof Player &&
            in_array($action->getActionName(), ActionEnum::getForceGetUpActions()) &&
            $lyingDownStatus = $actionParameter->getStatusByName(PlayerStatusEnum::LYING_DOWN)
        ) {
            $actionParameter->removeStatus($lyingDownStatus);
        }

        $changingRoomPatrolshipActions = ActionEnum::getChangingRoomPatrolshipActions()->toArray();
        if ($event->hasTags($changingRoomPatrolshipActions, all: false)
            && $event->getActionResult() instanceof Fail
        ) {
            $this->handlePatrolshipManoeuvreDamage($event);
        }
    }

    private function handlePatrolshipManoeuvreDamage(ActionEvent $event): void
    {
        $this->inflictDamageToDaedalus($event);
        $this->inflictDamageToPatrolShip($event);
        $this->inflictDamageToPlayer($event);
    }

    private function inflictDamageToDaedalus(ActionEvent $event): void
    {
        $patrolShipMechanic = $this->getPatrolShipMechanic($event);
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection(
            $patrolShipMechanic->getFailedManoeuvreDaedalusDamage()
        );

        $daedalusVariableModifierEvent = new DaedalusVariableEvent(
            $event->getAuthor()->getDaedalus(),
            DaedalusVariableEnum::HULL,
            -$damage,
            $event->getTags(),
            new \DateTime(),
        );

        $this->eventService->callEvent($daedalusVariableModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function inflictDamageToPatrolShip(ActionEvent $event): void
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $event->getActionParameter();
        /** @var PatrolShip $patrolShipMechanic */
        $patrolShipMechanic = $this->getPatrolShipMechanic($event);

        /** @var ChargeStatus $patrolShipArmor */
        $patrolShipArmor = $patrolShip->getStatusByName(EquipmentStatusEnum::PATROL_SHIP_ARMOR);
        if ($patrolShipArmor === null) {
            throw new \LogicException("Patrol ship {$patrolShip->getName()} should have an armor status");
        }

        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection(
            $patrolShipMechanic->getFailedManoeuvrePatrolShipDamage()
        );
        $patrolShipArmor->addCharge(-$damage);
        $this->statusService->persist($patrolShipArmor);

        $this->roomLogService->createLog(
            logKey: LogEnum::PATROL_DAMAGE,
            place: $event->getAuthor()->getPlace(),
            visibility: VisibilityEnum::PRIVATE,
            type: 'event_log',
            player: $event->getAuthor(),
            parameters: ['quantity' => $damage],
            dateTime: new \DateTime()
        );
    }

    private function inflictDamageToPlayer(ActionEvent $event): void
    {
        $patrolShipMechanic = $this->getPatrolShipMechanic($event);
        $damage = (int) $this->randomService->getSingleRandomElementFromProbaCollection(
            $patrolShipMechanic->getFailedManoeuvrePlayerDamage()
        );

        $playerModifierEvent = new PlayerVariableEvent(
            $event->getAuthor(),
            PlayerVariableEnum::HEALTH_POINT,
            -$damage,
            $event->getTags(),
            new \DateTime(),
        );

        $playerModifierEvent->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }

    private function getPatrolShipMechanic(ActionEvent $event): PatrolShip
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $event->getActionParameter();
        /** @var PatrolShip $patrolShipMechanic */
        $patrolShipMechanic = $patrolShip->getEquipment()->getMechanics()->filter(fn (Mechanic $mechanic) => in_array(EquipmentMechanicEnum::PATROL_SHIP, $mechanic->getMechanics()))->first();

        return $patrolShipMechanic;
    }
}
