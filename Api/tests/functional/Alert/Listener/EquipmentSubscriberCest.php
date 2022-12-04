<?php

namespace Mush\Tests\Alert\Listener;

use App\Tests\FunctionalTester;
use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Enum\ActionEnum;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\Neron;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EquipmentSubscriberCest
{
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testDestroyBrokenEquipment(FunctionalTester $I)
    {
        $statusConfig = new StatusConfig();
        $statusConfig
            ->setName(EquipmentStatusEnum::BROKEN)
            ->setVisibility(VisibilityEnum::PUBLIC);

        $I->haveInRepository($statusConfig);

        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['statusConfigs' => new ArrayCollection([$statusConfig])]);

        $neron = new Neron();
        $neron->setIsInhibited(true);
        $I->haveInRepository($neron);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class, ['gameConfig' => $gameConfig, 'neron' => $neron]);

        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;
        $I->haveInRepository($channel);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        /** @var EquipmentConfig $gravitySimulatorConfig */
        $gravitySimulatorConfig = $I->have(EquipmentConfig::class, [
            'name' => EquipmentEnum::GRAVITY_SIMULATOR,
            'gameConfig' => $gameConfig,
        ]);

        $gravitySimulator = new GameEquipment($room);
        $gravitySimulator
            ->setName($gravitySimulatorConfig->getName())
            ->setEquipment($gravitySimulatorConfig)
        ;

        $I->haveInRepository($gravitySimulator);

        $broken = new Status($gravitySimulator, $statusConfig);
        $I->haveInRepository($broken);

        $reportedAlert = new AlertElement();
        $reportedAlert->setEquipment($gravitySimulator);
        $I->haveInRepository($reportedAlert);

        $alertBroken = new Alert();
        $alertBroken
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::BROKEN_EQUIPMENTS)
            ->addAlertElement($reportedAlert)
        ;

        $I->haveInRepository($alertBroken);

        $alertGravity = new Alert();
        $alertGravity
            ->setDaedalus($daedalus)
            ->setName(AlertEnum::NO_GRAVITY)
        ;

        $I->haveInRepository($alertGravity);

        $I->seeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
        $I->seeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS]);

        $equipmentEvent = new EquipmentEvent(
            $gravitySimulator,
            false,
            VisibilityEnum::HIDDEN,
            ActionEnum::DISASSEMBLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::NO_GRAVITY]);
        $I->dontSeeInRepository(Alert::class, ['daedalus' => $daedalus, 'name' => AlertEnum::BROKEN_EQUIPMENTS]);
    }
}
