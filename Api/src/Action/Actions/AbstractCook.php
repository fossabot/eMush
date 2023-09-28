<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Cookable;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractCook extends AbstractAction
{
    protected GameEquipmentServiceInterface $gameEquipmentService;
    protected StatusServiceInterface $statusService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
    }

    protected function support(?LogParameterInterface $support, array $parameters): bool
    {
        return $support instanceof GameEquipment && !$support instanceof Door;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Cookable(['groups' => ['visibility']]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $support */
        $support = $this->support;
        $time = new \DateTime();

        if ($support->getEquipment()->getEquipmentName() === GameRationEnum::STANDARD_RATION) {
            $this->gameEquipmentService->transformGameEquipmentToEquipmentWithName(
                GameRationEnum::COOKED_RATION,
                $support,
                $this->player,
                $this->getAction()->getActionTags(),
                new \DateTime(),
                VisibilityEnum::PUBLIC
            );
        } elseif ($support->getStatusByName(EquipmentStatusEnum::FROZEN)) {
            $this->statusService->removeStatus(
                EquipmentStatusEnum::FROZEN,
                $support,
                $this->getAction()->getActionTags(),
                $time
            );
        }
    }
}
