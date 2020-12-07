<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\SuccessRateServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Dismountable;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Disassemble extends AttemptAction
{
    protected string $name = ActionEnum::DISASSEMBLE;

    private GameEquipment $gameEquipment;

    private RoomLogServiceInterface $roomLogService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        SuccessRateServiceInterface $successRateService,
        StatusServiceInterface $statusService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($randomService, $successRateService, $eventDispatcher, $statusService);

        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->gameConfig = $gameConfigService->getConfig();
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!($equipment = $actionParameters->getItem()) && 
            !($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->player = $player;
        $this->gameEquipment = $equipment;

        $dismountableType = $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::DISMOUNTABLE);
        if ($dismountableType !== null) {
            $this->actionCost->setActionPointCost($dismountableType->getActionCost());
        }
    }

    public function canExecute(): bool
    {
        $dismountableType = $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::DISMOUNTABLE);
        //Check that the item is reachable
        return null !== $dismountableType &&
            $this->player->canReachEquipment($this->gameEquipment) &&
            in_array(SkillEnum::TECHNICIAN, $this->player->getSkills())
        ;
    }

    protected function applyEffects(): ActionResult
    {
        $modificator = 1; //@TODO: skills, wrench
        $dismountableType = $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::DISMOUNTABLE);

        $response = $this->makeAttempt($dismountableType->getChancesSuccess(), $modificator);

        if ($response instanceof Success) {
            $this->disasemble($dismountableType);
        }

        $this->playerService->persist($this->player);

        return $response;
    }

    private function disasemble(Dismountable $dismountableType)
    {
        // add the item produced by disassembling
        foreach ($dismountableType->getProducts() as $productString => $number) {
            for ($i = 0; $i < $number; ++$i) {
                $productEquipment = $this
                    ->gameEquipmentService
                    ->createGameEquipmentFromName($productString, $this->player->getDaedalus())
                ;
                if ($this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory() &&
                    $productEquipment instanceof GameItem) {
                    $productEquipment->setPlayer($this->player);
                } else {
                    $productEquipment->setRoom($this->player->getRoom());
                }
                $this->gameEquipmentService->persist($productEquipment);
            }
        }

        // remove the dismanteled equipment
        $this->gameEquipment->removeLocation();
        $this->gameEquipmentService->delete($this->gameEquipment);
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::DISASSEMBLE,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
