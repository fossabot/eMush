<?php

namespace Mush\Modifier\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Event\EnhancePercentageRollEvent;
use Mush\Action\Event\PreparePercentageRollEvent;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomService;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\Config\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Service\RoomLogServiceInterface;

class ModifierService implements ModifierServiceInterface
{

    private EntityManagerInterface $entityManager;
    private EventServiceInterface $eventService;
    private RoomLogServiceInterface $logService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RoomLogServiceInterface $logService,
        RandomServiceInterface $randomService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->logService = $logService;
        $this->randomService = $randomService;
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

    public function isSuccessfulWithModifier(
        ModifierHolder $holder,
        int $baseSuccessRate,
        array $reasons,
        bool $tryToSucceed = true
    ) : bool {
        $successThreshold = $this->randomService->getSuccessThreshold();

        $event = new PreparePercentageRollEvent(
            $holder,
            $baseSuccessRate,
            $reasons[count($reasons) - 1],
            new \DateTime()
        );

        for ($i=count($reasons)-2; $i>=0; $i--) {
            $event->addReason($reasons[$i]);
        }

        $this->eventService->callEvent($event, PreparePercentageRollEvent::ACTION_ROLL_RATE);
        $successRate = $event->getRate();

        if ($tryToSucceed) {
            if ($successThreshold <= $successRate) {
                return true;
            }
        } else {
            if ($successThreshold > $successRate) {
                return true;
            }
        }

        return $this->enhancePercentageRoll($holder, $successRate, $tryToSucceed, $reasons);
    }

    private function enhancePercentageRoll(
        ModifierHolder $holder,
        int $successRate,
        bool $tryToSucceed,
        array $reasons
    ) : bool {
        $event = new EnhancePercentageRollEvent(
            $holder,
            $successRate,
            $tryToSucceed,
            $reasons[count($reasons) - 1],
            new \DateTime()
        );

        for ($i=count($reasons)-2; $i>=0; $i--) {
            $event->addReason($reasons[$i]);
        }

        $eventName = $tryToSucceed
            ? EnhancePercentageRollEvent::ACTION_TRY_TO_SUCCEED_ROLL_RATE
            : EnhancePercentageRollEvent::ACTION_TRY_TO_FAIL_ROLL_RATE;
        $this->eventService->callEvent($event, $eventName);

        $modifier = $event->getModifierConfig();
        if ($modifier !== null) {
            $this->logEnhancement($holder, $modifier);
            return $tryToSucceed;
        } else {
            return !$tryToSucceed;
        }
    }

    private function logEnhancement(ModifierHolder $holder, ModifierConfig $modifier) {
        if (!$holder instanceof Player) {
            return;
        }

        $this->logService->createLog(
            $modifier->getLogKeyWhenApplied(),
            $holder->getPlace(),
            VisibilityEnum::PRIVATE,
            "modifier_log",
            $holder
        );
    }

    public function createModifier(ModifierConfig $config, ModifierHolder $holder) : Modifier
    {
        $modifier = new Modifier($holder, $config);
        $this->persist($modifier);
        return $modifier;
    }

    public function deleteModifier(ModifierConfig $modifierConfig, ModifierHolder $holder): void {
        $modifier = $holder->getModifiers()->getModifierFromConfig($modifierConfig);
        $this->delete($modifier);
    }

    public function getHolderFromConfig(
        ModifierConfig $config,
        ModifierHolder $holder,
        ModifierHolder $target = null
    ) : ModifierHolder {
        $reach = $config->getReach();

        if ($holder instanceof Daedalus) {
            if ($reach === ModifierReachEnum::DAEDALUS) {
                return $holder;
            }
        }

        if ($holder instanceof Place) {
            if ($reach === ModifierReachEnum::DAEDALUS) {
                return $holder->getDaedalus();
            }

            if ($reach === ModifierReachEnum::PLACE) {
                return $holder;
            }
        }

        if ($holder instanceof GameEquipment) {
            return $this->getEquipmentHolder($holder, $reach);
        }

        if ($holder instanceof Player) {
            if ($target !== null) {
                if ($target instanceof Player) {
                    return $this->getPlayerHolder($holder, $target, $reach);
                } else {
                    throw new \LogicException('Target is not a player.');
                }
            } else {
                return $this->getPlayerHolder($holder, null, $reach);
            }
        }

        throw new \LogicException($holder->getClassName() .' can\'t have a ' . $reach . ' reach.');
    }

    private function getEquipmentHolder(GameEquipment $holder, string $reach) : ModifierHolder {
        switch ($reach) {
            case ModifierReachEnum::DAEDALUS:
                return $holder->getPlace()->getDaedalus();

            case ModifierReachEnum::PLACE:
                return $holder->getPlace();

            case ModifierReachEnum::EQUIPMENT:
                return $holder;

            case ModifierReachEnum::PLAYER:
                $player = $holder->getHolder();
                if ($player instanceof Player) {
                    return $player;
                } else {
                    throw new \LogicException('Equipment without a holder have a ' . $reach . ' reach.');
                }

            default:
                throw new \LogicException('Equipment don\'t have a ' . $reach . ' reach.');
        }
    }

    private function getPlayerHolder(Player $holder, Player | null $target, string $reach) : ModifierHolder {
        switch ($reach) {
            case ModifierReachEnum::DAEDALUS:
                return $holder->getPlace()->getDaedalus();

            case ModifierReachEnum::PLACE:
                return $holder->getPlace();

            case ModifierReachEnum::EQUIPMENT:
                throw new \LogicException('Player can\'t have a ' . $reach . ' reach.');

            case ModifierReachEnum::PLAYER:
                return $holder;

            case ModifierReachEnum::TARGET_PLAYER:
                if ($target === null) {
                    throw new \LogicException('Target is null.');
                } else {
                    return $target;
                }

            default:
                throw new \LogicException('Player don\'t have a ' . $reach . ' reach.');
        }
    }

}
