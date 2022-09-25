<?php

namespace Mush\Disease\Listener;

use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Disease\Entity\Collection\SymptomConfigCollection;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Repository\DiseaseCausesConfigRepository;
use Mush\Disease\Service\PlayerDiseaseServiceInterface;
use Mush\Disease\Service\SymptomConditionServiceInterface;
use Mush\Disease\Service\SymptomServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActionSubscriber implements EventSubscriberInterface
{
    private const CONTACT_DISEASE_RATE = 1;

    private DiseaseCausesConfigRepository $diseaseCausesConfigRepository;
    private PlayerDiseaseServiceInterface $playerDiseaseService;
    private RandomServiceInterface $randomService;
    private SymptomServiceInterface $symptomService;
    private SymptomConditionServiceInterface $symptomConditionService;

    public function __construct(
        DiseaseCausesConfigRepository $diseaseCausesConfigRepository,
        RandomServiceInterface $randomService,
        PlayerDiseaseServiceInterface $playerDiseaseService,
        SymptomServiceInterface $symptomService,
        SymptomConditionServiceInterface $symptomConditionService)
    {
        $this->diseaseCausesConfigRepository = $diseaseCausesConfigRepository;
        $this->randomService = $randomService;
        $this->playerDiseaseService = $playerDiseaseService;
        $this->symptomService = $symptomService;
        $this->symptomConditionService = $symptomConditionService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ActionEvent::POST_ACTION => 'onPostAction',
        ];
    }

    public function onPostAction(ActionEvent $event): void
    {
        $this->handleContactDiseases($event);
        $this->handlePostActionSymptoms($event);
    }

    private function getPlayerSymptomConfigs(Player $player): SymptomConfigCollection
    {
        $playerDiseases = $player->getMedicalConditions()->getActiveDiseases();

        $symptomConfigs = new SymptomConfigCollection([]);
        foreach ($playerDiseases as $disease) {
            foreach ($disease->getDiseaseConfig()->getSymptomConfigs() as $symptomConfig) {
                if (!$symptomConfigs->contains($symptomConfig)) {
                    $symptomConfigs->add($symptomConfig);
                }
            }
        }

        return $symptomConfigs;
    }

    private function handleContactDiseases(ActionEvent $event): void
    {
        if ($event->getAction()->getName() !== ActionEnum::MOVE) {
            return;
        }

        $player = $event->getPlayer();
        $otherPlayersInRoom = $player->getPlace()->getPlayers()->filter(function ($otherPlayer) use ($player) {
            return $otherPlayer->getId() !== $player->getId();
        });

        if ($otherPlayersInRoom->isEmpty()) {
            return;
        }

        $playerDiseases = $player->getMedicalConditions()->getActiveDiseases();
        $playerDiseases = $playerDiseases->map(function ($disease) {
            return $disease->getDiseaseConfig()->getName();
        })->toArray();

        if (count($playerDiseases) === 0) {
            return;
        }

        $contactDiseases = array_keys(
            $this->diseaseCausesConfigRepository->findCausesByDaedalus(
                DiseaseCauseEnum::CONTACT,
                $player->getDaedalus())
                ->getDiseases());

        if (count(array_intersect($playerDiseases, $contactDiseases)) === 0) {
            return;
        }

        $diseaseToTransmit = $this->randomService->getRandomElements($contactDiseases, 1)[0] ?? null;
        $playerToTransmitTo = $this->randomService->getRandomElements($otherPlayersInRoom->toArray(), 1)[0] ?? null;

        if ($diseaseToTransmit === null || $playerToTransmitTo === null) {
            return;
        }

        if ($this->randomService->isSuccessful(self::CONTACT_DISEASE_RATE)) {
            $this->playerDiseaseService->createDiseaseFromName($diseaseToTransmit, $playerToTransmitTo, DiseaseCauseEnum::CONTACT);
        }
    }

    private function handlePostActionSymptoms(ActionEvent $event): void
    {
        $player = $event->getPlayer();
        $action = $event->getAction();

        $postActionSymptomConfigs = $this->getPlayerSymptomConfigs($player)->getTriggeredSymptoms([ActionEvent::POST_ACTION]);
        $postActionSymptomConfigs = $this->symptomConditionService->getActiveSymptoms($postActionSymptomConfigs, $player, $action->getName(), $action);

        foreach ($postActionSymptomConfigs as $symptomConfig) {
            $this->symptomService->handlePostActionSymptom($symptomConfig, $player, $event->getTime());
        }
    }
}
