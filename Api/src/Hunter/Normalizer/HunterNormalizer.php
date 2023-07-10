<?php

declare(strict_types=1);

namespace Mush\Hunter\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Hunter\Entity\Hunter;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\HunterStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class HunterNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Hunter && !$data->isInPool();
    }

    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        /** @var Hunter $hunter */
        $hunter = $object;
        /** @var ChargeStatus $hunterCharges */
        $hunterCharges = $hunter->getStatusByName(HunterStatusEnum::HUNTER_CHARGE);
        $hunterKey = $hunter->getName();
        $isHunterAnAsteroid = $hunterKey === HunterEnum::ASTEROID;

        return [
            'id' => $hunter->getId(),
            'key' => $hunterKey,
            'name' => $this->translationService->translate(
                key: $hunterKey,
                parameters: [],
                domain: 'hunter',
                language: $hunter->getDaedalus()->getLanguage()
            ),
            'description' => $this->translationService->translate(
                key: $hunterKey . '_description',
                parameters: [
                    'charges' => $isHunterAnAsteroid ? $hunterCharges->getCharge() : null,
                    'health' => $hunter->getHealth(),
                ],
                domain: 'hunter',
                language: $hunter->getDaedalus()->getLanguage()
            ),
            'health' => $hunter->getHealth(),
            'charges' => $isHunterAnAsteroid ? $hunterCharges->getCharge() : null,
        ];
    }
}
