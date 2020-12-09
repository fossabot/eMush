<?php

namespace Mush\Equipment\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Equipment\Entity\GameItem;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ItemPileNormalizer implements ContextAwareNormalizerInterface
{
    private EquipmentNormalizer $equipmentNormalizer;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EquipmentNormalizer $equipmentNormalizer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->equipmentNormalizer = $equipmentNormalizer;
        $this->tokenStorage = $tokenStorage;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Collection;
    }

    /**
     * @param Collection $equipments
     *
     * @return array
     */
    public function normalize($equipments, string $format = null, array $context = [])
    {
        $piles = [];

        $items=$equipments->filter(fn (GameEquipment $equipment) => $equipment instanceof GameItem);

        

        foreach($items as $item){
            $itemName=$item->getEquipment()->getName();
            $itemStatuses=$item->getStatuses();

            if((!$item->GetStatusByName(EquipmentStatusEnum::HIDDEN) ||
                    ($item->GetStatusByName(EquipmentStatusEnum::HIDDEN) &&
                    $item->GetStatusByName(EquipmentStatusEnum::HIDDEN)->getPlayer()===$this->getUser()->getCurrentGame()))){

                if ($item->getEquipment()->isStackable() &&
                    count(array_filter($piles, function ($pile) use ($itemName, $itemStatuses)
                            {return $pile['key'] === $itemName && $this->compareStatusesForPiles($itemStatuses, $pile['statuses']);}))>0){

                    //@TODO mush player see contaminated rations in a different pile
                    //@TODO if ration is contaminated put it on top of the pile

                    $pileKey=array_search(current(array_filter($piles, function ($pile) use ($itemName, $itemStatuses)
                            {return $pile['key'] === $itemName && $this->compareStatusesForPiles($itemStatuses, $pile['statuses']);})), $piles);
                    
                    if (array_key_exists('number', $piles[$pileKey])){
                        $piles[$pileKey]['number'] = $piles[$pileKey]['number']+1;
                    }else{
                        $piles[$pileKey]['number'] = 2;
                    }

                } else{
                        $piles[]=$this->equipmentNormalizer->normalize($item);
                }
            }
        };

        return $piles;
    }

    private function getUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    private function compareStatusesForPiles(Collection $itemStatuses, Collection $pileStatuses): bool
    {
        //if the item is a doc stack the one with the same content (ie same document_content status)
        $statusName=EquipmentStatusEnum::DOCUMENT_CONTENT;
        if (($itemStatuses->filter(fn (Status $status) => ($status->getName()===$statusName))->isEmpty()!==
            $pileStatuses->filter(fn (Status $status) => ($status->getName()===$statusName))->isEmpty()) ||
            (!$itemStatuses->filter(fn (Status $status) => ($status->getName()===$statusName))->isEmpty() &&
            $itemStatuses->filter(fn (Status $status) => ($status->getName()===$statusName))->first()->getContent()!==
            $pileStatuses->filter(fn (Status $status) => ($status->getName()===$statusName))->first()->getContent())){
            return false;
        }

        // in other cases check that the status on the item are the same (ie same Name)
        foreach(EquipmentStatusEnum::splitItemPileStatus() as $statusName){
            if ($itemStatuses->filter(fn (Status $status) => ($status->getName()===$statusName))->isEmpty()!==
                $pileStatuses->filter(fn (Status $status) => ($status->getName()===$statusName))->isEmpty()){
                    return false;
            }
        }

        return true;
    }
}
