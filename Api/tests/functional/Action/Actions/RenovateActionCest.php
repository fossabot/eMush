<?php

declare(strict_types=1);

namespace Mush\Tests\functional\Action\Actions;

use Mush\Action\Actions\Renovate;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Entity\PlaceConfig;
use Mush\Place\Enum\RoomEnum;
use Mush\RoomLog\Entity\RoomLog;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Config\ChargeStatusConfig;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class RenovateActionCest extends AbstractFunctionalTest
{
    private Renovate $renovateAction;
    private Action $action;
    private Place $alphaBay2;

    private StatusServiceInterface $statusService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->createExtraRooms($I, $this->daedalus);

        $this->alphaBay2 = $this->daedalus->getPlaceByName(RoomEnum::ALPHA_BAY_2);

        $this->player1->changePlace($this->alphaBay2);

        $this->action = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::RENOVATE]);
        $this->renovateAction = $I->grabService(Renovate::class);
        $this->statusService = $I->grabService(StatusServiceInterface::class);
    }

    public function testRenovateSuccess(FunctionalTester $I): void
    {
        $this->action->setSuccessRate(100);

        /** @var EquipmentConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->alphaBay2);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatusConfig $pasiphaeArmorStatusConfig */
        $pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);

        $pasiphaeArmorStatusConfig->setStartCharge($pasiphaeArmorStatusConfig->getMaxCharge() - 1);
        /** @var ChargeStatus $pasiphaeArmor */
        $pasiphaeArmorStatus = $this->statusService->createStatusFromConfig(
            $pasiphaeArmorStatusConfig,
            $pasiphae,
            [],
            new \DateTime()
        );

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $pasiphae,
            tags: ['test'],
            time: new \DateTime()
        );

        $maxCharge = $pasiphaeArmorStatusConfig->getMaxCharge();

        /** @var EquipmentConfig $metalScrapConfig */
        $metalScrapConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::METAL_SCRAPS]);
        $metalScrap = new GameEquipment($this->alphaBay2);
        $metalScrap
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($metalScrapConfig)
        ;
        $I->haveInRepository($metalScrap);

        $this->renovateAction->loadParameters($this->action, $this->player1, $pasiphae);
        $I->assertTrue($this->renovateAction->isVisible());

        $I->assertNotEquals(
            expected: $maxCharge,
            actual: $pasiphaeArmorStatus->getCharge(),
        );

        $result = $this->renovateAction->execute();
        $I->assertInstanceOf(Success::class, $result);

        $I->assertFalse(
            $this->alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS)
        );
        $I->assertEquals(
            expected: $maxCharge,
            actual: $pasiphaeArmorStatus->getCharge(),
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::RENOVATE_SUCCESS,
            'visibility' => VisibilityEnum::PUBLIC,
        ]);
        $I->assertFalse($pasiphae->hasStatus(EquipmentStatusEnum::BROKEN));
    }

    public function testRenovateFail(FunctionalTester $I): void
    {
        $this->action->setSuccessRate(0);

        /** @var EquipmentConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->alphaBay2);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatusConfig $pasiphaeArmorStatusConfig */
        $pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);
        $pasiphaeArmorStatusConfig->setStartCharge($pasiphaeArmorStatusConfig->getMaxCharge() - 1);
        /** @var ChargeStatus $pasiphaeArmor */
        $pasiphaeArmorStatus = $this->statusService->createStatusFromConfig(
            $pasiphaeArmorStatusConfig,
            $pasiphae,
            [],
            new \DateTime()
        );

        $maxCharge = $pasiphaeArmorStatusConfig->getMaxCharge();

        /** @var EquipmentConfig $metalScrapConfig */
        $metalScrapConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::METAL_SCRAPS]);
        $metalScrap = new GameEquipment($this->alphaBay2);
        $metalScrap
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($metalScrapConfig)
        ;
        $I->haveInRepository($metalScrap);

        $this->renovateAction->loadParameters($this->action, $this->player1, $pasiphae);
        $I->assertTrue($this->renovateAction->isVisible());

        $I->assertNotEquals(
            expected: $maxCharge,
            actual: $pasiphaeArmorStatus->getCharge(),
        );

        $result = $this->renovateAction->execute();
        $I->assertInstanceOf(Fail::class, $result);

        $I->assertFalse(
            $this->alphaBay2->hasEquipmentByName(ItemEnum::METAL_SCRAPS)
        );
        $I->assertNotEquals(
            expected: $maxCharge,
            actual: $pasiphaeArmorStatus->getCharge(),
        );
        $I->seeInRepository(RoomLog::class, [
            'place' => RoomEnum::ALPHA_BAY_2,
            'daedalusInfo' => $this->daedalus->getDaedalusInfo(),
            'playerInfo' => $this->player1->getPlayerInfo(),
            'log' => ActionLogEnum::RENOVATE_FAIL,
            'visibility' => VisibilityEnum::PRIVATE,
        ]);
    }

    public function testRenovateNotVisibleIfPatrolShipNotBrokenAndNotDamaged(FunctionalTester $I): void
    {
        /** @var EquipmentConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->alphaBay2);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatusConfig $pasiphaeArmorStatusConfig */
        $pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);
        /** @var ChargeStatus $pasiphaeArmor */
        $pasiphaeArmorStatus = $this->statusService->createStatusFromConfig(
            $pasiphaeArmorStatusConfig,
            $pasiphae,
            [],
            new \DateTime()
        );

        /** @var EquipmentConfig $metalScrapConfig */
        $metalScrapConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::METAL_SCRAPS]);
        $metalScrap = new GameEquipment($this->alphaBay2);
        $metalScrap
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($metalScrapConfig)
        ;
        $I->haveInRepository($metalScrap);

        $this->renovateAction->loadParameters($this->action, $this->player1, $pasiphae);
        $I->assertFalse($this->renovateAction->isVisible());
    }

    public function testRenovateActionIsVisibleIfPatrolShipIsBroken(FunctionalTester $I): void
    {
        /** @var EquipmentConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->alphaBay2);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        /** @var EquipmentConfig $metalScrapConfig */
        $metalScrapConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::METAL_SCRAPS]);
        $metalScrap = new GameEquipment($this->alphaBay2);
        $metalScrap
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($metalScrapConfig)
        ;
        $I->haveInRepository($metalScrap);

        /** @var ChargeStatusConfig $pasiphaeArmorStatusConfig */
        $pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);
        $pasiphaeArmorStatus = new ChargeStatus($pasiphae, $pasiphaeArmorStatusConfig);

        $this->statusService->createStatusFromName(
            statusName: EquipmentStatusEnum::BROKEN,
            holder: $pasiphae,
            tags: ['test'],
            time: new \DateTime()
        );

        $this->renovateAction->loadParameters($this->action, $this->player1, $pasiphae);
        $I->assertTrue($this->renovateAction->isVisible());
    }

    public function testRenovateActionIsVisibleIfPatrolShipIsDamaged(FunctionalTester $I): void
    {
        /** @var EquipmentConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->alphaBay2);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        /** @var EquipmentConfig $metalScrapConfig */
        $metalScrapConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => ItemEnum::METAL_SCRAPS]);
        $metalScrap = new GameEquipment($this->alphaBay2);
        $metalScrap
            ->setName(ItemEnum::METAL_SCRAPS)
            ->setEquipment($metalScrapConfig)
        ;
        $I->haveInRepository($metalScrap);

        /** @var ChargeStatusConfig $pasiphaeArmorStatusConfig */
        $pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);
        $pasiphaeArmorStatus = new ChargeStatus($pasiphae, $pasiphaeArmorStatusConfig);

        $maxCharge = $pasiphaeArmorStatusConfig->getMaxCharge();
        $pasiphaeArmorStatus->setCharge($maxCharge - 1);

        $this->renovateAction->loadParameters($this->action, $this->player1, $pasiphae);
        $I->assertTrue($this->renovateAction->isVisible());
    }

    public function testRenovateNotVisibleIfNoScrapAvailable(FunctionalTester $I): void
    {
        /** @var EquipmentConfig $pasiphaeConfig */
        $pasiphaeConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::PASIPHAE]);
        $pasiphae = new GameEquipment($this->alphaBay2);
        $pasiphae
            ->setName(EquipmentEnum::PASIPHAE)
            ->setEquipment($pasiphaeConfig)
        ;
        $I->haveInRepository($pasiphae);

        /** @var ChargeStatusConfig $pasiphaeArmorStatusConfig */
        $pasiphaeArmorStatusConfig = $I->grabEntityFromRepository(ChargeStatusConfig::class, ['name' => EquipmentStatusEnum::PATROL_SHIP_ARMOR . '_pasiphae_default']);

        $pasiphaeArmorStatusConfig->setStartCharge($pasiphaeArmorStatusConfig->getMaxCharge() - 1);
        /** @var ChargeStatus $pasiphaeArmorStatus */
        $pasiphaeArmorStatus = $this->statusService->createStatusFromConfig(
            $pasiphaeArmorStatusConfig,
            $pasiphae,
            [],
            new \DateTime()
        );

        $this->renovateAction->loadParameters($this->action, $this->player1, $pasiphae);
        $I->assertFalse($this->renovateAction->isVisible());
    }

    private function createExtraRooms(FunctionalTester $I, Daedalus $daedalus): void
    {
        $alphaBay2Config = $I->grabEntityFromRepository(PlaceConfig::class, ['placeName' => RoomEnum::ALPHA_BAY_2]);
        $alphaBay2 = new Place();
        $alphaBay2
            ->setName(RoomEnum::ALPHA_BAY_2)
            ->setType($alphaBay2Config->getType())
            ->setDaedalus($daedalus)
        ;
        $I->haveInRepository($alphaBay2);

        $I->refreshEntities($daedalus);
    }
}
