<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\DailySporesLimit;
use Mush\Action\Validator\MushSpore;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Status;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Infect extends AbstractAction
{
    protected string $name = ActionEnum::INFECT;

    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->statusService = $statusService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof Player;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Status(['status' => PlayerStatusEnum::MUSH, 'target' => Status::PLAYER, 'groups' => ['visibility']]));
        $metadata->addConstraint(new MushSpore(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::INFECT_NO_SPORE]));
        $metadata->addConstraint(new Status([
            'status' => PlayerStatusEnum::MUSH,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::INFECT_MUSH,
        ]));
        $metadata->addConstraint(new Status([
            'status' => PlayerStatusEnum::IMMUNIZED,
            'contain' => false,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::INFECT_IMMUNE,
        ]));
        $metadata->addConstraint(new DailySporesLimit(['target' => DailySporesLimit::PLAYER, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::INFECT_DAILY_LIMIT]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Player $parameter */
        $parameter = $this->parameter;

        $playerEvent = new PlayerEvent($parameter);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::INFECTION_PLAYER);

        /** @var ChargeStatus $sporeStatus */
        $sporeStatus = $this->player->getStatusByName(PlayerStatusEnum::SPORES);
        $sporeStatus->addCharge(-1);
        $this->statusService->persist($sporeStatus);

        /** @var ChargeStatus $mushStatus */
        $mushStatus = $this->player->getStatusByName(PlayerStatusEnum::MUSH);
        $mushStatus->addCharge(-1);
        $this->statusService->persist($mushStatus);

        return new Success($this->parameter);
    }
}
