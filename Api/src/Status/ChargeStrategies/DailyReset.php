<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Game\Service\CycleServiceInterface;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Service\StatusServiceInterface;

class DailyReset extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::DAILY_RESET;

    private CycleServiceInterface $cycleService;

    public function __construct(
        StatusServiceInterface $statusService,
        CycleServiceInterface $cycleService
    ) {
        $this->cycleService = $cycleService;

        parent::__construct($statusService);
    }

    public function apply(ChargeStatus $status): void
    {
        $daedalus = $this->statusService->getDaedalus($status);

        //Only applied on cycle 1
        if ($daedalus->getCycle() !== 1 || $status->getCharge() >= $status->getThreshold()) {
            return;
        }
        $status->setCharge($status->getThreshold() ?? 0);
    }
}
