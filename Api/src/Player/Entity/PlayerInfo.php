<?php

namespace Mush\Player\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Repository\PlayerRepository;
use Mush\User\Entity\User;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class PlayerInfo
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private int $id;

    #[ORM\OneToOne(inversedBy: 'playerInfo', targetEntity: Player::class, cascade: ['ALL'])]
    private ?Player $player;

    #[ORM\ManyToOne(targetEntity: User::class)]
    private User $user;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $gameStatus;

    #[ORM\ManyToOne(targetEntity: CharacterConfig::class)]
    private CharacterConfig $characterConfig;

    #[ORM\OneToOne(inversedBy: 'playerInfo', targetEntity: ClosedPlayer::class, cascade: ['ALL'])]
    private ClosedPlayer $closedPlayer;

    public function __construct(
        Player $player,
        User $user,
        CharacterConfig $characterConfig
    ) {
        $this->player = $player;
        $this->user = $user;
        $this->characterConfig = $characterConfig;
        $this->closedPlayer = new ClosedPlayer();
        $this->gameStatus = GameStatusEnum::CURRENT;

        $this->player->setPlayerInfo($this);
        $this->closedPlayer->setPlayerInfo($this);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlayer(): ?Player
    {
        return $this->player;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getName(): string
    {
        return $this->characterConfig->getName();
    }

    public function getGameStatus(): string
    {
        return $this->gameStatus;
    }

    public function isAlive(): bool
    {
        return $this->gameStatus === GameStatusEnum::CURRENT;
    }

    public function setGameStatus(string $gameStatus): static
    {
        $this->gameStatus = $gameStatus;

        return $this;
    }

    public function getCharacterConfig(): CharacterConfig
    {
        return $this->characterConfig;
    }

    public function getClosedPlayer(): ClosedPlayer
    {
        return $this->closedPlayer;
    }
}
