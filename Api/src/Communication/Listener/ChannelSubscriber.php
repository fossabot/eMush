<?php

namespace Mush\Communication\Listener;

use Mush\Communication\Enum\ChannelScopeEnum;
use Mush\Communication\Enum\CommunicationActionEnum;
use Mush\Communication\Enum\NeronMessageEnum;
use Mush\Communication\Event\ChannelEvent;
use Mush\Communication\Services\ChannelPlayerServiceInterface;
use Mush\Communication\Services\ChannelServiceInterface;
use Mush\Communication\Services\MessageServiceInterface;
use Mush\Player\Event\PlayerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ChannelSubscriber implements EventSubscriberInterface
{
    private ChannelServiceInterface $channelService;
    private ChannelPlayerServiceInterface $channelPlayerService;
    private MessageServiceInterface $messageService;

    public function __construct(
        ChannelServiceInterface $channelService,
        ChannelPlayerServiceInterface $channelPlayerService,
        MessageServiceInterface $messageService
    ) {
        $this->channelService = $channelService;
        $this->channelPlayerService = $channelPlayerService;
        $this->messageService = $messageService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ChannelEvent::NEW_CHANNEL => 'onNewChannel',
            ChannelEvent::JOIN_CHANNEL => 'onJoinChannel',
            ChannelEvent::EXIT_CHANNEL => 'onExitChannel',
        ];
    }

    public function onNewChannel(ChannelEvent $event): void
    {
        $channel = $event->getChannel();

        if ($player = $event->getPlayer()) {
            $this->channelService->invitePlayer($player, $channel);
        }
    }

    public function onJoinChannel(ChannelEvent $event): void
    {
        $channel = $event->getChannel();

        if ($player = $event->getPlayer()) {
            $this->channelPlayerService->addPlayer($player, $channel);

            $this->messageService->createSystemMessage(
                NeronMessageEnum::PLAYER_ENTER_CHAT,
                $channel,
                ['character' => $player->getCharacterConfig()->getName()],
                new \DateTime()
            );
        }
    }

    public function onExitChannel(ChannelEvent $event): void
    {
        $channel = $event->getChannel();

        if ($player = $event->getPlayer()) {
            $this->channelPlayerService->removePlayer($player, $channel);

            $key = $this->createPlayerLeaveMessage($event->getReason());

            $this->messageService->createSystemMessage(
                $key,
                $channel,
                ['character' => $player->getCharacterConfig()->getName()],
                new \DateTime()
            );
        }

        if ($channel->getScope() === ChannelScopeEnum::PRIVATE && $channel->getParticipants()->isEmpty()) {
            $this->channelService->deleteChannel($channel);
        }
    }

    private function createPlayerLeaveMessage(string $reason): string
    {
        switch ($reason) {
            case CommunicationActionEnum::EXIT:
                return NeronMessageEnum::PLAYER_LEAVE_CHAT;

            case PlayerEvent::DEATH_PLAYER:
                return NeronMessageEnum::PLAYER_LEAVE_CHAT_DEATH;

            default:
                throw new \LogicException('unknown leave chat reason');
        }
    }
}
