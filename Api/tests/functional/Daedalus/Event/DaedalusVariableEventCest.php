<?php

namespace functional\Daedalus\Event;

use App\Tests\FunctionalTester;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Entity\DaedalusConfig;
use Mush\Daedalus\Entity\DaedalusInfo;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Daedalus\Event\DaedalusVariableEvent;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\LocalizationConfig;
use Mush\Game\Enum\EventEnum;
use Mush\Game\Event\AbstractQuantityEvent;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\Entity\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierHolderClassEnum;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Place\Entity\Place;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DaedalusVariableEventCest
{
    private EventDispatcherInterface $eventDispatcher;

    public function _before(FunctionalTester $I)
    {
        $this->eventDispatcher = $I->grabService(EventDispatcherInterface::class);
    }

    public function testChangeOxygenWithTanks(FunctionalTester $I)
    {
        /** @var DaedalusConfig $daedalusConfig */
        $daedalusConfig = $I->have(DaedalusConfig::class);
        /** @var GameConfig $gameConfig */
        $gameConfig = $I->have(GameConfig::class, ['daedalusConfig' => $daedalusConfig]);

        /** @var Daedalus $daedalus */
        $daedalus = $I->have(Daedalus::class);
        $daedalus->setDaedalusVariables($daedalusConfig);
        $daedalus->setOxygen(32);
        /** @var LocalizationConfig $localizationConfig */
        $localizationConfig = $I->have(LocalizationConfig::class, ['name' => 'test']);
        $daedalusInfo = new DaedalusInfo($daedalus, $gameConfig, $localizationConfig);
        $I->haveInRepository($daedalusInfo);

        /** @var Place $room */
        $room = $I->have(Place::class, ['daedalus' => $daedalus]);

        $event = new DaedalusVariableEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            -2,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($event, AbstractQuantityEvent::CHANGE_VARIABLE);

        $I->assertEquals(30, $daedalus->getOxygen());

        // add an oxygen tank
        $modifierActivationRequirement = new ModifierActivationRequirement(ModifierRequirementEnum::REASON);
        $modifierActivationRequirement
            ->setActivationRequirement(EventEnum::NEW_CYCLE)
            ->buildName()
        ;
        $I->haveInRepository($modifierActivationRequirement);

        $modifierConfig = new ModifierConfig();
        $modifierConfig
            ->setTargetVariable(DaedalusVariableEnum::OXYGEN)
            ->setDelta(1)
            ->setModifierHolderClass(ModifierHolderClassEnum::DAEDALUS)
            ->setTargetEvent(AbstractQuantityEvent::CHANGE_VARIABLE)
            ->addModifierRequirement($modifierActivationRequirement)
            ->buildName()
        ;
        $I->haveInRepository($modifierConfig);

        $modifier = new GameModifier($daedalus, $modifierConfig);
        $I->haveInRepository($modifier);

        $event = new DaedalusVariableEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            -2,
            'other_reason',
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($event, AbstractQuantityEvent::CHANGE_VARIABLE);

        $I->assertEquals(28, $daedalus->getOxygen());

        $event = new DaedalusVariableEvent(
            $daedalus,
            DaedalusVariableEnum::OXYGEN,
            -2,
            EventEnum::NEW_CYCLE,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($event, AbstractQuantityEvent::CHANGE_VARIABLE);

        $I->assertEquals(27, $daedalus->getOxygen());
    }
}
