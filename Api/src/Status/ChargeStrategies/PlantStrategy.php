<?php

namespace Mush\Status\ChargeStrategies;

use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Service\StatusServiceInterface;

class PlantStrategy extends AbstractChargeStrategy
{
    protected string $name = ChargeStrategyTypeEnum::PLANT;

    public function __construct(StatusServiceInterface $statusService)
    {
        parent::__construct($statusService);
    }

    public function apply(ChargeStatus $status)
    {
        if ($status->getCharge() >= $status->getThreshold()) {
            return;
        }

        //@TODO: Handle garden
        $status->addCharge(1);
    }
}
