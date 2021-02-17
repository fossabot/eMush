<?php

namespace Mush\Action\Normalizer;

use Mush\Action\Actions\AttemptAction;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Service\ActionStrategyServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ActionNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;
    private ActionStrategyServiceInterface $actionStrategyService;
    private ActionServiceInterface $actionService;

    public function __construct(
        TranslatorInterface $translator,
        ActionStrategyServiceInterface $actionStrategyService,
        ActionServiceInterface $actionService
    ) {
        $this->translator = $translator;
        $this->actionStrategyService = $actionStrategyService;
        $this->actionService = $actionService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Action;
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $actionClass = $this->actionStrategyService->getAction($object->getName());
        if (!$actionClass) {
            return [];
        }

        if (!($currentPlayer = $context['currentPlayer'] ?? null)) {
            throw new \LogicException('Current player is missing from context');
        }

        $actionParameter = new ActionParameters();
        if (array_key_exists('player', $context)) {
            $actionParameter->setPlayer($context['player']);
        }
        if (array_key_exists('door', $context)) {
            $actionParameter->setDoor($context['door']);
        }
        if (array_key_exists('item', $context)) {
            $actionParameter->setItem($context['item']);
        }
        if (array_key_exists('equipment', $context)) {
            $actionParameter->setEquipment($context['equipment']);
        }

        $actionClass->loadParameters($object, $currentPlayer, $actionParameter);

        if ($actionClass->isVisible()) {
            $actionName = $object->getName();

            $normalizedAction = [
                'id' => $object->getId(),
                'name' => $this->translator->trans("{$actionName}.name", [], 'actions'),
                'actionPointCost' => $this->actionService->getTotalActionPointCost($currentPlayer, $object),
                'movementPointCost' => $this->actionService->getTotalMovementPointCost($currentPlayer, $object),
                'moralPointCost' => $this->actionService->getTotalMoralPointCost($currentPlayer, $object),
                ];

            if ($actionClass instanceof AttemptAction) {
                $normalizedAction['successRate'] = $actionClass->getSuccessRate();
            }

            if ($reason = $actionClass->isImpossible()) {
                $normalizedAction['description'] = $this->translator->trans("{$reason}.description", [], 'actionsFail');
                $normalizedAction['isPossible'] = false;
            } else {
                $normalizedAction['description'] = $this->translator->trans("{$actionName}.description", [], 'actions');
                $normalizedAction['isPossible'] = true;
            }

            return $normalizedAction;
        }

        return [];
    }
}
