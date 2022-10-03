<?php

namespace Mush\Modifier\Entity\Condition;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Entity\GameItem;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\StatusEnum;

#[ORM\Entity]
class EquipmentRemainChargesModifierCondition extends ModifierCondition
{
    private string $equipmentName;

    public function __construct(string $equipmentName)
    {
        parent::__construct();
        $this->equipmentName = $equipmentName;
    }

    public function isTrue(ModifierHolder $holder, RandomServiceInterface $randomService): bool
    {
        if ($holder instanceof Player) {
            $items = $holder->getEquipments()->getByName($this->equipmentName);

            /* @var GameItem $item */
            foreach ($items as $item) {
                if ($item->hasStatus(StatusEnum::CHARGE)) {
                    /* @var ChargeStatus $charge */
                    $charge = $item->getStatusByName(StatusEnum::CHARGE);
                    if ($charge->getCharge() > 0) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
