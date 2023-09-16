<?php

namespace functional\Status\Listener;

use App\Tests\AbstractFunctionalTest;
use App\Tests\FunctionalTester;
use Mush\Game\Service\EventServiceInterface;
use Mush\Hunter\Event\HunterPoolEvent;

class HunterCycleSubscriberCest extends AbstractFunctionalTest
{
    private EventServiceInterface $eventService;

    public function _before(FunctionalTester $I)
    {
        parent::_before($I);
        $this->eventService = $I->grabService(EventServiceInterface::class);
    }

    public function testOnHunterCreation(FunctionalTester $I)
    {
        $poolEvent = new HunterPoolEvent($this->daedalus, ['test'], new \DateTime());
        $this->eventService->callEvent($poolEvent, HunterPoolEvent::UNPOOL_HUNTERS);
        $I->assertCount(4, $this->daedalus->getAttackingHunters());
    }
}
