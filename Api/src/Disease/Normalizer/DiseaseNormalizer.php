<?php

namespace Mush\Disease\Normalizer;

use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\Config\SymptomCondition;
use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\SymptomConditionEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Modifier\Entity\ModifierCondition;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class DiseaseNormalizer implements ContextAwareNormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof PlayerDisease;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $diseaseConfig = $object->getDiseaseConfig();

        $description = $this->translationService->translate("{$diseaseConfig->getName()}.description", [], 'disease');

        $description = $this->getSymptomEffects($diseaseConfig, $description);
        $description = $this->getModifierEffects($diseaseConfig, $description);

        return [
            'key' => $diseaseConfig->getName(),
            'name' => $this->translationService->translate($diseaseConfig->getName() . '.name', [], 'disease'),
            'type' => $diseaseConfig->getType(),
            'description' => $description,
       ];
    }

    private function getSymptomEffects(DiseaseConfig $diseaseConfig, string $description): string
    {
        // first get symptom effects
        /** @var SymptomConfig $symptomConfig */
        foreach ($diseaseConfig->getSymptomConfigs() as $symptomConfig) {
            $name = $symptomConfig->getName();

            $randomCondition = $symptomConfig->getSymptomConditions()
                ->filter(fn (SymptomCondition $condition) => $condition->getName() === SymptomConditionEnum::RANDOM);
            if (!$randomCondition->isEmpty()) {
                $chance = $randomCondition->first()->getValue();
            } else {
                $chance = 100;
            }

            $effect = $this->translationService->translate($name . '.description', ['chance' => $chance], 'modifiers');

            if ($effect) {
                $description = $description . '//' . $effect;
            }
        }

        return $description;
    }

    private function getModifierEffects(DiseaseConfig $diseaseConfig, string $description): string
    {
        // Get Modifier effect description
        /** @var ModifierConfig $modifierConfig */
        foreach ($diseaseConfig->getModifierConfigs() as $modifierConfig) {
            $delta = $modifierConfig->getDelta();
            $mode = $modifierConfig->getMode();
            $scope = $modifierConfig->getScope();
            $target = $modifierConfig->getTarget();

            if ($mode == ModifierModeEnum::MULTIPLICATIVE) {
                if ($delta < 1) {
                    $key = $modifierConfig->getScope() . '_decrease';
                } else {
                    $key = $modifierConfig->getScope() . '_increase';
                }
                $delta = (1 - $delta) * 100;
            } else {
                if ($delta < 0) {
                    $key = $modifierConfig->getScope() . '_decrease';
                } else {
                    $key = $modifierConfig->getScope() . '_increase';
                }
            }

            $emoteMap = PlayerVariableEnum::getEmoteMap();
            if (isset($emoteMap[$scope])) {
                $emote = $emoteMap[$scope];
            } elseif (isset($emoteMap[$target])) {
                $emote = $emoteMap[$target];
            } else {
                $emote = '';
            }

            $randomCondition = $modifierConfig->getModifierConditions()
                ->filter(fn (ModifierCondition $condition) => $condition->getName() === SymptomConditionEnum::RANDOM);
            if (!$randomCondition->isEmpty()) {
                $chance = $randomCondition->first()->getValue();
            } else {
                $chance = 100;
            }

            $parameters = [
                'chance' => $chance,
                'emote' => $emote,
                'quantity' => abs($delta),
            ];

            $effect = $this->translationService->translate($key . '.description', $parameters, 'modifiers');

            if ($effect) {
                $description = $description . '//' . $effect;
            }
        }

        return $description;
    }
}
