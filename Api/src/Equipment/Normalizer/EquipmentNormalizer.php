<?php

namespace Mush\Equipment\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\ConsumableDiseaseAttribute;
use Mush\Disease\Service\ConsumableDiseaseServiceInterface;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Ration;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\EquipmentEffectService;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class EquipmentNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;
    private GearToolServiceInterface $gearToolService;
    private ConsumableDiseaseServiceInterface $consumableDiseaseService;
    private EquipmentEffectService $equipmentEffectService;

    public function __construct(
        TranslationServiceInterface $translationService,
        GearToolServiceInterface $gearToolService,
        ConsumableDiseaseServiceInterface $consumableDiseaseService,
        EquipmentEffectService $equipmentEffectService
    ) {
        $this->translationService = $translationService;
        $this->gearToolService = $gearToolService;
        $this->consumableDiseaseService = $consumableDiseaseService;
        $this->equipmentEffectService = $equipmentEffectService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof GameEquipment;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        if (!($currentPlayer = $context['currentPlayer'] ?? null)) {
            throw new \LogicException('Current player is missing from context');
        }

        if ($object instanceof Door) {
            $context['door'] = $object;
            $type = 'door';
        } elseif ($object instanceof GameItem) {
            $context['item'] = $object;
            $type = 'items';
        } else {
            $context['equipment'] = $object;
            $type = 'equipments';
        }

        $statuses = [];
        foreach ($object->getStatuses() as $status) {
            $normedStatus = $this->normalizer->normalize($status, $format, array_merge($context, ['equipment' => $object]));
            if (is_array($normedStatus) && count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        return [
            'id' => $object->getId(),
            'key' => $object->getName(),
            'name' => $this->translationService->translate($object->getName() . '.name', [], $type),
            'description' => $this->translationService->translate("{$object->getName()}.description", [], $type),
            'statuses' => $statuses,
            'actions' => $this->getActions($object, $currentPlayer, $format, $context),
            'effects' => $this->getRationsEffect($object, $currentPlayer->getDaedalus()),
        ];
    }

    private function getActions(GameEquipment $gameEquipment, Player $currentPlayer, ?string $format, array $context): array
    {
        $actions = [];

        $contextActions = $this->getContextActions($gameEquipment, $currentPlayer);

        /** @var Action $action */
        foreach ($contextActions as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        $actionsObject = $gameEquipment->getEquipment()->getActions()
            ->filter(fn (Action $action) => $action->getScope() === ActionScopeEnum::CURRENT)
        ;

        /** @var Action $action */
        foreach ($actionsObject as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, $context);
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        return $actions;
    }

    private function getContextActions(GameEquipment $gameEquipment, Player $currentPlayer): Collection
    {
        $scopes = [ActionScopeEnum::ROOM];
        $scopes[] = ($gameEquipment->getPlace() instanceof Place) ? ActionScopeEnum::SHELVE : ActionScopeEnum::INVENTORY;

        if ($gameEquipment instanceof GameItem) {
            $target = GameItem::class;
        } else {
            $target = null;
        }

        return $this->gearToolService->getActionsTools($currentPlayer, $scopes, $target);
    }

    private function getRationsEffect(GameEquipment $gameEquipment, Daedalus $daedalus): array
    {
        /** @var Ration $ration */
        $ration = $gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::RATION);
        if ($ration === null) {
            return [];
        }

        $effects = [];

        $consumableDiseaseEffect = $this->consumableDiseaseService->findConsumableDiseases($gameEquipment->getName(), $daedalus);
        if ($consumableDiseaseEffect !== null) {
            /** @var ConsumableDiseaseAttribute $disease */
            foreach ($consumableDiseaseEffect->getDiseases() as $disease) {
                $effects[] = $this->createDiseaseLine($disease);
            }

            /** @var ConsumableDiseaseAttribute $cure */
            foreach ($consumableDiseaseEffect->getCures() as $cure) {
                $effects[] = $this->createCureLine($cure);
            }
        }

        return [
            'title' => $this->translationService->translate('ration_data', [], 'misc'),
            'effects' => array_merge($effects, $this->createConsummableLines($this->equipmentEffectService->getConsumableEffect($ration, $daedalus))),
        ];
    }

    private function createConsummableLines(ConsumableEffect $consumableEffect): array
    {
        $effects = [];

        $satiety = $consumableEffect->getSatiety();
        if ($satiety !== null) {
            $effects[] = $this->createEffectLine($satiety, 'satiety_point');
        }
        $actionPoint = $consumableEffect->getActionPoint();
        if ($actionPoint !== null) {
            $effects[] = $this->createEffectLine($actionPoint, 'action_point');
        }
        $movementPoint = $consumableEffect->getMovementPoint();
        if ($movementPoint !== null) {
            $effects[] = $this->createEffectLine($movementPoint, 'movement_point');
        }
        $healthPoint = $consumableEffect->getHealthPoint();
        if ($healthPoint !== null) {
            $effects[] = $this->createEffectLine($healthPoint, 'health_point');
        }
        $moralPoint = $consumableEffect->getMoralPoint();
        if ($moralPoint !== null) {
            $effects[] = $this->createEffectLine($moralPoint, 'moral_point');
        }

        return $effects;
    }

    private function createDiseaseLine(ConsumableDiseaseAttribute $disease): string
    {
        $diseaseName = $this->translationService->translate($disease->getDisease() . '.name', [], 'disease');

        if ($disease->getDelayMin() > 0) {
            $key = 'delayed_disease_info';
            $params = [
                'quantity' => $disease->getRate(),
                'diseaseName' => $diseaseName,
                'start' => $disease->getDelayMin(),
                'end' => $disease->getDelayMin() + $disease->getDelayLength(),
            ];
        } else {
            $key = 'disease_info';
            $params = [
                'quantity' => $disease->getRate(),
                'diseaseName' => $diseaseName,
            ];
        }

        return $this->translationService->translate($key, $params, 'misc');
    }

    private function createCureLine(ConsumableDiseaseAttribute $cure): string
    {
        $cureName = $this->translationService->translate($cure->getDisease() . '.name', [], 'disease');

        if ($cure->getDelayMin() > 0) {
            $key = 'delayed_cure_info';
            $params = [
                'diseaseName' => $cureName,
                'start' => $cure->getDelayMin(),
                'end' => $cure->getDelayMin() + $cure->getDelayLength(),
            ];
        } else {
            $key = 'cure_info';
            $params = [
                'diseaseName' => $cureName,
            ];
        }

        return $this->translationService->translate($key, $params, 'misc');
    }

    private function createEffectLine(int $quantity, string $key): string
    {
        $sign = $quantity > 0 ? '+' : '-';

        return "{$sign} {$quantity} {$this->translationService->translate($key, [], 'misc')}";
    }
}
