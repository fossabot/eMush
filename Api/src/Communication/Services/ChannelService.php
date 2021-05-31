<?php

namespace Mush\Communication\Services;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Communication\Entity\Channel;
use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Event\ChannelEvent;
use Mush\Communication\Repository\ChannelPlayerRepository;
use Mush\Communication\Repository\ChannelRepository;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChannelService implements ChannelServiceInterface
{
    private EntityManagerInterface $entityManager;
    private ChannelRepository $channelRepository;
    private ChannelPlayerRepository $channelPlayerRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        ChannelRepository $channelRepository,
        ChannelPlayerRepository $channelPlayerRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->channelRepository = $channelRepository;
        $this->channelPlayerRepository = $channelPlayerRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getPlayerChannels(Player $player, bool $privateOnly = false): Collection
    {
        return $this->channelRepository->findByPlayer($player, $privateOnly);
    }

    public function getPublicChannel(Daedalus $daedalus): ?Channel
    {
        return $this->channelRepository->findOneBy([
            'daedalus' => $daedalus,
            'scope' => ChannelScopeEnum::PUBLIC,
        ]);
    }

    public function createPublicChannel(Daedalus $daedalus): Channel
    {
        $channel = new Channel();
        $channel
            ->setDaedalus($daedalus)
            ->setScope(ChannelScopeEnum::PUBLIC)
        ;

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        return $channel;
    }

    public function createPrivateChannel(Player $player): Channel
    {
        $channel = new Channel();
        $channel
            ->setDaedalus($player->getDaedalus())
            ->setScope(ChannelScopeEnum::PRIVATE)
        ;

        $this->entityManager->persist($channel);
        $this->entityManager->flush();

        $event = new ChannelEvent($channel, $player);
        $this->eventDispatcher->dispatch($event, ChannelEvent::NEW_CHANNEL);

        return $channel;
    }

    public function getInvitablePlayersToPrivateChannel(Channel $channel): PlayerCollection
    {
        $maxPrivateChannel = $channel->getDaedalus()->getGameConfig()->getMaxNumberPrivateChannel();

        return new PlayerCollection($this->channelPlayerRepository->findAvailablePlayerForPrivateChannel($channel, $maxPrivateChannel));
    }

    public function invitePlayer(Player $player, Channel $channel): Channel
    {
        $event = new ChannelEvent($channel, $player);
        $this->eventDispatcher->dispatch($event, ChannelEvent::JOIN_CHANNEL);

        return $channel;
    }

    public function exitChannel(Player $player, Channel $channel): bool
    {
        $event = new ChannelEvent($channel, $player);
        $this->eventDispatcher->dispatch($event, ChannelEvent::EXIT_CHANNEL);

        return true;
    }

    public function deleteChannel(Channel $channel): bool
    {
        $this->entityManager->remove($channel);
        $this->entityManager->flush();

        return true;
    }
}
