<?php

declare(strict_types=1);

namespace Mush\Tests\Functional\Action\Actions;

use Mush\Action\Actions\Scan;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\Config\EquipmentConfig;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Exploration\Entity\Planet;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Mush\Tests\AbstractFunctionalTest;
use Mush\Tests\FunctionalTester;

final class ScanCest extends AbstractFunctionalTest
{
    private Action $scanActionConfig;
    private Scan $scanAction;

    private StatusServiceInterface $statusService;

    private Place $bridge;
    private GameEquipment $astroTerminal;

    public function _before(FunctionalTester $I): void
    {
        parent::_before($I);

        $this->scanActionConfig = $I->grabEntityFromRepository(Action::class, ['name' => ActionEnum::SCAN]);
        $this->scanAction = $I->grabService(Scan::class);

        $this->statusService = $I->grabService(StatusServiceInterface::class);

        // given there is an astro terminal on the bridge
        $this->bridge = $this->createExtraPlace(RoomEnum::BRIDGE, $I, $this->daedalus);
        $astroTerminalConfig = $I->grabEntityFromRepository(EquipmentConfig::class, ['equipmentName' => EquipmentEnum::ASTRO_TERMINAL]);
        $this->astroTerminal = new GameEquipment($this->bridge);
        $this->astroTerminal
            ->setName(EquipmentEnum::ASTRO_TERMINAL)
            ->setEquipment($astroTerminalConfig)
        ;
        $I->haveInRepository($this->astroTerminal);

        // given player is on the bridge
        $this->player->changePlace($this->bridge);

        // given player is focused on the astro terminal
        $this->statusService->createStatusFromName(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
            target: $this->astroTerminal
        );
    }

    public function testScanNotVisibleIfPlayerNotFocusedOnAstroTerminal(FunctionalTester $I): void
    {
        // given player is not focused on the astro terminal
        $this->statusService->removeStatus(
            statusName: PlayerStatusEnum::FOCUSED,
            holder: $this->player,
            tags: [],
            time: new \DateTime(),
        );

        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        // then the action is not visible
        $I->assertFalse($this->scanAction->isVisible());
    }

    public function testScanSuccessCreatesAPlanet(FunctionalTester $I): void
    {
        // when player scans
        $this->scanAction->loadParameters($this->scanActionConfig, $this->player, $this->astroTerminal);
        $this->scanAction->execute();

        // then a planet is created
        $I->seeInRepository(Planet::class);
    }
}