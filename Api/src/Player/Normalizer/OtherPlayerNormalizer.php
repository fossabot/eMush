<?php

namespace Mush\Player\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class OtherPlayerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        TranslationServiceInterface $translationService,
        GearToolServiceInterface $gearToolService
    ) {
        $this->translationService = $translationService;
        $this->gearToolService = $gearToolService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        $currentPlayer = $context['currentPlayer'] ?? null;

        return $data instanceof Player && $data !== $currentPlayer;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;

        $language = $player->getDaedalus()->getLanguage();

        $character = $player->getName();

        $playerData = [
            'id' => $player->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translationService->translate(
                    $character . '.name',
                    [],
                    'characters',
                    $player->getDaedalus()->getLanguage()
                ),
                'description' => $this->translationService->translate(
                    $character . '.description',
                    [],
                    'characters',
                    $player->getDaedalus()->getLanguage()
                ),
                'skills' => $player->getPlayerInfo()->getCharacterConfig()->getSkills(),
            ],
        ];

        if (isset($context['currentPlayer'])) {
            /** @var Player $currentPlayer */
            $currentPlayer = $context['currentPlayer'];
            $statuses = [];
            foreach ($player->getStatuses() as $status) {
                $normedStatus = $this->normalizer->normalize($status, $format, array_merge($context, ['player' => $player]));
                if (is_array($normedStatus) && count($normedStatus) > 0) {
                    $statuses[] = $normedStatus;
                }
            }

            // if current player is mush add spores info
            if ($currentPlayer->isMush() && !$player->hasStatus(PlayerStatusEnum::IMMUNIZED)) {
                $normedSpores = [
                    'key' => PlayerStatusEnum::SPORES,
                    'name' => $this->translationService->translate(PlayerStatusEnum::SPORES . '.name', [], 'status', $language),
                    'description' => $this->translationService->translate(PlayerStatusEnum::SPORES . '.description', [], 'status', $language),
                    'charge' => $player->getSpores(),
                ];
                $statuses[] = $normedSpores;
            }

            $playerData['statuses'] = $statuses;
            $playerData['skills'] = $player->getSkills();
            $playerData['actions'] = $this->getActions($player, $format, $context);
        }

        return $playerData;
    }

    private function getActions(Player $player, ?string $format, array $context): array
    {
        $contextualActions = $this->getContextActions($context['currentPlayer']);

        $actions = [];

        /** @var Action $action */
        foreach ($player->getTargetActions() as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, array_merge($context, ['player' => $player]));
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        /** @var Action $action */
        foreach ($contextualActions as $action) {
            $normedAction = $this->normalizer->normalize($action, $format, array_merge($context, ['player' => $player]));
            if (is_array($normedAction) && count($normedAction) > 0) {
                $actions[] = $normedAction;
            }
        }

        return $actions;
    }

    private function getContextActions(Player $currentPlayer): Collection
    {
        $scope = [ActionScopeEnum::OTHER_PLAYER];

        return $this->gearToolService->getActionsTools($currentPlayer, $scope);
    }
}
