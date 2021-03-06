<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\ParameterHasAction;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Status;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Disassemble extends AttemptAction
{
    protected string $name = ActionEnum::DISASSEMBLE;

    /** @var GameEquipment */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator,
            $randomService,
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameEquipment;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new ParameterHasAction(['groups' => ['visibility']]));
        $metadata->addConstraint(new Reach(['groups' => ['visibility']]));
        $metadata->addConstraint(new Status([
            'status' => EquipmentStatusEnum::REINFORCED, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::DISMANTLE_REINFORCED,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        $response = $this->makeAttempt();

        if ($response instanceof Success) {
            $this->disasemble();
        }

        $this->playerService->persist($this->player);

        $target = new Target($this->parameter->getName(), 'items');
        $response->setTarget($target);

        return $response;
    }

    private function disasemble(): void
    {
        // add the item produced by disassembling
        foreach ($this->parameter->getEquipment()->getDismountedProducts() as $productString => $number) {
            for ($i = 0; $i < $number; ++$i) {
                $productEquipment = $this
                    ->gameEquipmentService
                    ->createGameEquipmentFromName($productString, $this->player->getDaedalus())
                ;
                $equipmentEvent = new EquipmentEvent($productEquipment, VisibilityEnum::HIDDEN);
                $equipmentEvent->setPlayer($this->player);
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

                $this->gameEquipmentService->persist($productEquipment);
            }
        }

        // remove the dismantled equipment
        $equipmentEvent = new EquipmentEvent($this->parameter, VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
