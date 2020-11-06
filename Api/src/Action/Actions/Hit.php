<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionCost;
use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class Hit extends Action
{
    protected const NAME = ActionEnum::HIT;
    
    
    private PlayerServiceInterface $playerService;
    private RandomServiceInterface $randomService;

    public function __construct(
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService
    ) {
        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->actionCost = new ActionCost();
        $this->actionCost->setActionPointCost(1);
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (! ($target = $actionParameters->getPlayer())) {
            throw new \InvalidArgumentException('Invalid target parameter');
        }

        $this->player  = $player;
        $this->target  = $target;
         $this->chance_success = 50;
         $this->damage=0;
    }

    public function canExecute(): bool
    {
        return ($this->player->getRoom()===$this->target->getRoom() &&
                    $this->player!==$this->target);
    }

    protected function applyEffects(): ActionResult
    {
        if ($this->randomService->random(0, 100)< $this->chance_success) {
            // TODO: add log
        } else {
            $this->damage = $this->randomService->random(1, 3);
        
            if (in_array(SkillEnum::SOLID, $this->player->getSkills())) {
                $this->damage=$this->damage+1;
            }
            if (in_array(SkillEnum::WRESTLER, $this->player->getSkills())) {
                $this->damage=$this->damage+2;
            }
            if (in_array(SkillMushEnum::HARD_BOILED, $this->target->getSkills())) {
                $this->damage=$this->damage-1;
            }
            if ($this->target->hasItemByName(ItemEnum::PLASTENITE_ARMOR)) {
                $this->damage=$this->damage-1;
            }
            if ($this->damage<=0) {
                // TODO:
            } elseif ($this->target->getHealthPoint()>damage) {
                $this->target->setHealthPoint($this->target->getHealthPoint() - $this->damage);
                    
                $this->playerService->persist($this->target);
            } else {
                // TODO: kill the target
            }
        }
        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createPlayerLog(
            ActionEnum::HIT,
            $this->player->getRoom(),
            $this->player,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }


    public function getActionName(): string
    {
        return self::NAME;
    }
}
