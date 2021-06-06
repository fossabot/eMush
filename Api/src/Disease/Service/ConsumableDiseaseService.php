<?php

namespace Mush\Disease\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\ConsumableDisease;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Entity\ConsumableDiseaseConfig;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Repository\ConsumableDiseaseConfigRepository;
use Mush\Disease\Repository\ConsumableDiseaseRepository;
use Mush\Game\Service\RandomServiceInterface;

class ConsumableDiseaseService implements ConsumableDiseaseServiceInterface
{
    private ConsumableDiseaseRepository $consumableDiseaseRepository;
    private ConsumableDiseaseConfigRepository $consumableDiseaseConfigRepository;
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;

    public function __construct(
        ConsumableDiseaseRepository $consumableDiseaseRepository,
        ConsumableDiseaseConfigRepository $consumableDiseaseConfigRepository,
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomService
    ) {
        $this->consumableDiseaseRepository = $consumableDiseaseRepository;
        $this->consumableDiseaseConfigRepository = $consumableDiseaseConfigRepository;
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
    }

    public function findConsumableDiseases(string $name, Daedalus $daedalus): ?ConsumableDisease
    {
        $consumableDisease = $this->consumableDiseaseRepository->findOneBy(
            ['name' => $name, 'daedalus' => $daedalus]
        );

        if ($consumableDisease === null) {
            $consumableDisease = $this->createConsumableDiseases($name, $daedalus);
        }

        return $consumableDisease;
    }

    public function createConsumableDiseases(string $name, Daedalus $daedalus): ?ConsumableDisease
    {
        /** @var ConsumableDiseaseConfig $consumableDiseaseConfig */
        $consumableDiseaseConfig = $this->consumableDiseaseConfigRepository->findOneBy(['name' => $name, 'gameConfig' => $daedalus->getGameConfig()]);
        if ($consumableDiseaseConfig === null) {
            return null;
        }

        $consumableDisease = new ConsumableDisease();
        $consumableDisease
            ->setDaedalus($daedalus)
            ->setName($name)
        ;
        $this->entityManager->persist($consumableDisease);

        $effectsNumber = 0;
        // if the ration is a fruit 0 to 4 effects should be dispatched among diseases, cures and extraEffects
        if (count($consumableDiseaseConfig->getEffectNumber()) > 0) {
            $effectsNumber = intval($this->randomService->getSingleRandomElementFromProbaArray(
                $consumableDiseaseConfig->getEffectNumber()
            ));
        }

        $diseaseNumberPossible = count($consumableDiseaseConfig->getDiseasesName());
        $curesNumberPossible = count($consumableDiseaseConfig->getCuresName());

        if ($effectsNumber > 0) {
            // We chose 0 to 4 unique id for the effects
            $pickedEffects = $this->randomService->getRandomElements(
                range(
                    1,
                    $diseaseNumberPossible + $curesNumberPossible,
                    $effectsNumber
                )
            );

            //Get the number of cures, disease and special effect from the id
            $curesNumber = count(array_filter($pickedEffects, fn ($idEffect) => $idEffect <= $curesNumberPossible));

            $diseasesNumber = $effectsNumber - $curesNumber;

            if ($curesNumber > 0) {
                $this->createCuresFromConfigForConsumableDisease($consumableDisease, $consumableDiseaseConfig, $diseasesNumber);
            }

            if ($diseasesNumber > 0) {
                $this->createDiseasesFromConfigForConsumableDisease($consumableDisease, $consumableDiseaseConfig, $diseasesNumber);
            }
        }

        /** @var ConsumableDiseaseAttribute $disease */
        foreach ($consumableDiseaseConfig->getAttributes() as $disease) {
            $ConsumableDiseaseAttribute = new ConsumableDiseaseAttribute();
            $ConsumableDiseaseAttribute
                ->setConsumableDisease($consumableDisease)
                ->setDisease($disease->getDisease())
                ->setRate($disease->getRate())
                ->setDelayMin($disease->getDelayMin())
                ->setDelayLength($disease->getDelayLength())
            ;

            $this->entityManager->persist($ConsumableDiseaseAttribute);
        }

        $this->entityManager->flush();

        return $consumableDisease;
    }

    private function createDiseasesFromConfigForConsumableDisease(
        ConsumableDisease $consumableDisease,
        ConsumableDiseaseConfig $consumableDiseaseConfig,
        int $number
    ): ConsumableDisease {
        $diseasesNames = $this->randomService->getRandomElementsFromProbaArray($consumableDiseaseConfig->getDiseasesName(), $number);
        foreach ($diseasesNames as $diseaseName) {
            $diseaseCharacteristic = $this->createDiseaseCharacteristic($diseaseName, $consumableDiseaseConfig);
            $diseaseCharacteristic->setConsumableDisease($consumableDisease);
            $this->entityManager->persist($diseaseCharacteristic);
        }

        return $consumableDisease;
    }

    private function createCuresFromConfigForConsumableDisease(
        ConsumableDisease $consumableDisease,
        ConsumableDiseaseConfig $consumableDiseaseConfig,
        int $number
    ): ConsumableDisease {
        $diseasesNames = $this->randomService->getRandomElementsFromProbaArray($consumableDiseaseConfig->getCuresName(), $number);
        foreach ($diseasesNames as $diseaseName) {
            $diseaseCharacteristic = $this->createDiseaseCharacteristic($diseaseName, $consumableDiseaseConfig, TypeEnum::CURE);
            $diseaseCharacteristic->setConsumableDisease($consumableDisease);
            $this->entityManager->persist($diseaseCharacteristic);
        }

        return $consumableDisease;
    }

    private function createDiseaseCharacteristic(string $diseaseName, ConsumableDiseaseConfig $config, string $type = TypeEnum::DISEASE): ConsumableDiseaseAttribute
    {
        $ConsumableDiseaseAttribute = new ConsumableDiseaseAttribute();

        $rates = $type === TypeEnum::DISEASE ? $config->getDiseasesChances() : $config->getCuresChances();

        $ConsumableDiseaseAttribute
            ->setDisease($diseaseName)
            ->setType($type)
            ->setRate((int) $this->randomService->getSingleRandomElementFromProbaArray($rates))
        ;
        $delay = (int) $this->randomService->getSingleRandomElementFromProbaArray($config->getDiseasesDelayMin());

        if ($delay > 0) {
            $ConsumableDiseaseAttribute
                ->setDelayMin($delay)
                ->setDelayLength((int) $this->randomService->getSingleRandomElementFromProbaArray($config->getDiseasesDelayLength()));
        }

        return $ConsumableDiseaseAttribute;
    }
}
