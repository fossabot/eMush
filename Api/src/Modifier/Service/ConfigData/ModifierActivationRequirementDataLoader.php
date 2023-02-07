<?php

namespace Mush\Modifier\Service\ConfigData;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Service\ConfigData\ConfigDataLoader;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Repository\ModifierActivationRequirementRepository;

class ModifierActivationRequirementDataLoader extends ConfigDataLoader
{
    private EntityManagerInterface $entityManager;
    private ModifierActivationRequirementRepository $modifierActivationRequirementRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ModifierActivationRequirementRepository $modifierActivationRequirementRepository)
    {
        $this->entityManager = $entityManager;
        $this->modifierActivationRequirementRepository = $modifierActivationRequirementRepository;
    }

    public function loadConfigsData(): void
    {
        foreach (ModifierActivationRequirementData::$dataArray as $modifierActivationRequirementData) {
            $modifierActivationRequirement = $this->modifierActivationRequirementRepository->findOneBy(['name' => $modifierActivationRequirementData['name']]);

            if ($modifierActivationRequirement !== null) {
                continue;
            }

            $modifierActivationRequirement = new ModifierActivationRequirement($modifierActivationRequirementData['activationRequirementName']);
            $modifierActivationRequirement
                ->setName($modifierActivationRequirementData['name'])
                ->setActivationRequirement($modifierActivationRequirementData['activationRequirement'])
                ->setValue($modifierActivationRequirementData['value'])
            ;

            $this->entityManager->persist($modifierActivationRequirement);
        }
        $this->entityManager->flush();
    }
}
