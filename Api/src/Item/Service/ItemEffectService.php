<?php

namespace Mush\Item\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Item\Entity\ConsumableEffect;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Entity\Items\Ration;
use Mush\Item\Entity\PlantEffect;
use Mush\Item\Repository\ConsumableEffectRepository;
use Mush\Item\Repository\PlantEffectRepository;

class ItemEffectService implements ItemEffectServiceInterface
{
    private ConsumableEffectRepository $consumableEffectRepository;
    private PlantEffectRepository $plantEffectRepository;
    private RandomServiceInterface $randomService;

    /**
     * ItemEffectService constructor.
     */
    public function __construct(
        ConsumableEffectRepository $consumableEffectRepository,
        PlantEffectRepository $plantEffectRepository,
        RandomServiceInterface $randomService
    ) {
        $this->consumableEffectRepository = $consumableEffectRepository;
        $this->plantEffectRepository = $plantEffectRepository;
        $this->randomService = $randomService;
    }

    public function getConsumableEffect(Ration $ration, Daedalus $daedalus): ConsumableEffect
    {
        $consumableEffect = $this->consumableEffectRepository
            ->findOneBy(['ration' => $ration, 'daedalus' => $daedalus])
        ;

        if (null === $consumableEffect) {
            $consumableEffect = new ConsumableEffect();
            
            

            
            $consumableEffect
                ->setDaedalus($daedalus)
                ->setRation($ration)
                ->setActionPoint(current($this->randomService->getRandomElements($ration->getActionPoints())))
                ->setMovementPoint(current($this->randomService->getRandomElements($ration->getMovementPoints())))
                ->setHealthPoint(current($this->randomService->getRandomElements($ration->getHealthPoints())))
                ->setMoralPoint(current($this->randomService->getRandomElements($ration->getMoralPoints())))
                
               // if the ration is a fruit 0 to 4 effects shoulb be dispatched among diseases, cures and extraEffects
                if($ration instanceof fruit && $ration->getEffectsNumber()->count()>0){                   
                   $picked_effects=$this->randomService->getRandomElements(
	                   array_merge($ration->getCures(), $ration->getDiseases(), $ration->getExtraEffects()),
	                   current($this->randomService->getRandomElements($ration->getEffectsNumber()))
	                   );
                   $consumableEffect
                   	->setCures(array_intersect($picked_effects, $ration->getCures())
                   	->setDiseases(array_intersect($picked_effects, $ration->getDiseases())
                   	->setExtraEffects(array_intersect($picked_effects, $ration->getExtraEffects());
                	
                }else{
                	$consumableEffect
		                ->setCures($this->randomService->getRandomElements(
			                $ration->getCures(),
			                current($this->randomService->getRandomElements($ration->getCuresNumber()))
		                ))
		                ->setDiseases($this->randomService->getRandomElements(
			                $ration->getDiseases(), 
			                current($this->randomService->getRandomElements($ration->getDiseasesNumber()))
		                ))
		                ->setExtraEffects($this->randomService->getRandomElements(
			                $ration->getExtraEffects(),
			                current($this->randomService->getRandomElements($ration->getExtraEffectsNumber()))
		                ));
	             }

            $this->consumableEffectRepository->persist($consumableEffect);
        }

        return $consumableEffect;
    }

    public function getPlantEffect(Plant $plant, Daedalus $daedalus): PlantEffect
    {
        $plantEffect = $this->plantEffectRepository
            ->findOneBy(['plant' => $plant, 'daedalus' => $daedalus])
        ;

        if (null === $plantEffect) {
            $plantEffect = new PlantEffect();
            $plantEffect
                ->setDaedalus($daedalus)
                ->setPlant($plant)
                ->setMaturationTime(
                    $this->randomService->random(
                        $plant->getMinMaturationTime(),
                        $plant->getMaxMaturationTime()
                    )
                )
                ->setOxygen($this->randomService->random($plant->getMinOxygen(), $plant->getMaxOxygen()))
            ;

            $this->plantEffectRepository->persist($plantEffect);
        }

        return $plantEffect;
    }
}
