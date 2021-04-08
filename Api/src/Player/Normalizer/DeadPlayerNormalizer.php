<?php


namespace Mush\Player\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Entity\Action;
use Mush\Action\Enum\ActionScopeEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeadPlayerNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator,
    )
    {
        $this->translator = $translator;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        $currentPlayer = $context['currentPlayer'] ?? null;

        return $data instanceof Player && $data === $currentPlayer && $data->getGameStatus() === GameStatusEnum::FINISHED;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;

        $character = $player->getCharacterConfig()->getName();

        return [
            'id' => $player->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translator->trans($character . '.name', [], 'characters'),
            ],
            'triumph' => $player->getTriumph(),
        ];
    }

    private function getOtherPlayers(Player $player): array
    {
        $otherPlayers = [];
        foreach($player->getDaedalus()->getPlayers() as $otherPlayer){
            if($otherPlayer !== $player){

                $character = $otherPlayer->getCharacterConfig()->getName();

                $normalizedOtherPlayer = [
                    'id' => $player->getId(),
                    'character' => [
                        'key' => $character,
                        'value' => $this->translator->trans($character . '.name', [], 'characters'),
                        ],
                    'likes' => $player->getLikes(),
                    ];

                if ($otherPlayer->getGameStatus() !== GameStatusEnum::CURRENT){
                    $normalizedOtherPlayer['isDead'] = [
                        'day' => $otherPlayer->getDayDeath(),
                        'cycle' => $otherPlayer->getCycleDeath(),
                        'cause' => $otherPlayer->getEndStatus()
                    ];
                }else{
                    $normalizedOtherPlayer['isDead'] = false;
                }
                $otherPlayers[] = $normalizedOtherPlayer;
            }
        }
        return $otherPlayers;
    }
}


