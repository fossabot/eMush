<?php

namespace Mush\Status\Listener;

use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Status\Service\PlayerStatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerModifierSubscriber implements EventSubscriberInterface
{
    private PlayerStatusServiceInterface $playerStatus;

    public function __construct(
        PlayerStatusServiceInterface $playerStatus
    ) {
        $this->playerStatus = $playerStatus;
    }

    public static function getSubscribedEvents()
    {
        return [
            AbstractQuantityEvent::CHANGE_VARIABLE => ['onChangeVariable', -10], //Applied after player modification
        ];
    }

    public function onChangeVariable(AbstractQuantityEvent $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerModifierEvent) {
            return;
        }

        $player = $playerEvent->getPlayer();
        $date = $playerEvent->getTime();

        switch ($playerEvent->getModifiedVariable()) {
            case PlayerVariableEnum::MORAL_POINT:
                if (!$player->isMush()) {
                    $this->playerStatus->handleMoralStatus($player, $date);
                }

                return;
            case PlayerVariableEnum::SATIETY:
                $this->playerStatus->handleSatietyStatus($player, $date);

                return;
        }
    }
}
