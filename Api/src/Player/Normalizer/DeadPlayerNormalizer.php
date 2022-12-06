<?php

namespace Mush\Player\Normalizer;

use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class DeadPlayerNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService,
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        $currentPlayer = $context['currentPlayer'] ?? null;

        return $data instanceof Player &&
            $data === $currentPlayer &&
            $data->getPlayerInfo()->getGameStatus() === GameStatusEnum::FINISHED
        ;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;

        $daedalus = $player->getDaedalus();
        $language = $daedalus->getLanguage();
        $character = $player->getName();
        $playerInfo = $player->getPlayerInfo();
        $deadPlayerInfo = $playerInfo->getClosedPlayer();

        $endCause = $deadPlayerInfo->getEndCause();

        $playerData = [
            'id' => $player->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translationService->translate($character . '.name', [], 'characters', $language),
            ],
            'triumph' => $player->getTriumph(),
            'daedalus' => [
                'key' => $daedalus->getId(),
                'calendar' => [
                    'name' => $this->translationService->translate('calendar.name', [], 'daedalus', $language),
                    'description' => $this->translationService->translate('calendar.description', [], 'daedalus', $language),
                    'cycle' => $daedalus->getCycle(),
                    'day' => $daedalus->getDay(),
                ],
            ],
            'skills' => $player->getSkills(),
            'gameStatus' => $playerInfo->getGameStatus(),
            'endCause' => $this->normalizeEndReason($endCause, $language),
        ];

        $playerData['players'] = $this->getOtherPlayers($player, $language);

        return $playerData;
    }

    private function getOtherPlayers(Player $player, string $language): array
    {
        $otherPlayers = [];

        /** @var Player $otherPlayer */
        foreach ($player->getDaedalus()->getPlayers() as $otherPlayer) {
            if ($otherPlayer !== $player) {
                $character = $otherPlayer->getName();

                // TODO add likes
                $normalizedOtherPlayer = [
                    'id' => $otherPlayer->getId(),
                    'character' => [
                        'key' => $character,
                        'value' => $this->translationService->translate(
                            $character . '.name',
                            [],
                            'characters',
                            $language
                        ),
                        'description' => $this->translationService->translate(
                            $character . '.abstract',
                            [],
                            'characters',
                            $language
                        ),
                    ],
                ];

                $otherPlayerInfo = $otherPlayer->getPlayerInfo();
                $otherClosedPlayer = $otherPlayerInfo->getClosedPlayer();

                $normalizedOtherPlayer['likes'] = $otherClosedPlayer->getLikes();

                if ($otherPlayerInfo->getGameStatus() !== GameStatusEnum::CURRENT) {
                    $endCause = $otherClosedPlayer->getEndCause();
                    $normalizedOtherPlayer['isDead'] = [
                        'day' => $otherClosedPlayer->getDayDeath(),
                        'cycle' => $otherClosedPlayer->getCycleDeath(),
                        'cause' => $this->normalizeEndReason($endCause, $language),
                    ];
                } else {
                    $normalizedOtherPlayer['isDead'] = [
                        'day' => null,
                        'cycle' => null,
                        'cause' => $this->normalizeEndReason(EndCauseEnum::STILL_LIVING, $language),
                    ];
                }
                $otherPlayers[] = $normalizedOtherPlayer;
            }
        }

        return $otherPlayers;
    }

    private function normalizeEndReason(string $endCause, string $language): array
    {
        return [
            'key' => $endCause,
            'name' => $this->translationService->translate(
                $endCause . '.name',
                [],
                LanguageEnum::END_CAUSE,
                $language
            ),
            'description' => $this->translationService->translate(
                $endCause . '.description',
                [],
                LanguageEnum::END_CAUSE,
                $language
            ),
        ];
    }
}
