<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\Location;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\Status\Enum\EquipmentStatusEnum as EnumEquipmentStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Take extends AbstractAction
{
    protected string $name = ActionEnum::TAKE;

    /** @var GameItem */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Location(['location' => ReachEnum::SHELVE, 'groups' => ['visibility']]));
        $metadata->addConstraint(new InventoryFull(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::FULL_INVENTORY]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var ItemConfig $item */
        $item = $this->parameter->getEquipment();

        $this->parameter->setPlace(null);
        $this->parameter->setPlayer($this->player);

        // add BURDENED status if item is heavy
        if ($item->isHeavy()) {
            $this->statusService->createCoreStatus(PlayerStatusEnum::BURDENED, $this->player);
        }

        if ($hiddenStatus = $this->parameter->getStatusByName(EnumEquipmentStatusEnum::HIDDEN)) {
            $this->parameter->removeStatus($hiddenStatus);
            $this->player->removeStatus($hiddenStatus);
        }

        $this->gameEquipmentService->persist($this->parameter);
        $this->playerService->persist($this->player);

        $target = new Target($this->parameter->getName(), 'items');

        return new Success($target);
    }
}
