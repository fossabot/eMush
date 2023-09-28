<?php

namespace Mush\Action\Event;

use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Game\Event\AbstractGameEvent;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;

class ActionEvent extends AbstractGameEvent
{
    public const PRE_ACTION = 'pre.action';
    public const POST_ACTION = 'post.action';
    public const RESULT_ACTION = 'result.action';
    public const EXECUTE_ACTION = 'execute.action';

    private Action $action;
    private ?LogParameterInterface $actionSupport;
    private ?ActionResult $actionResult = null;
    private array $actionParameters = [];

    public function __construct(Action $action, Player $player, LogParameterInterface $actionSupport = null, array $actionParameters = [])
    {
        $this->action = $action;
        $this->author = $player;
        $this->actionSupport = $actionSupport;
        $this->actionParameters = $actionParameters;

        $tags = $action->getActionTags();
        if ($actionSupport !== null) {
            $tags[] = $actionSupport->getLogName();
        }

        parent::__construct($tags, new \DateTime());
    }

    public function getAuthor(): Player
    {
        $player = $this->author;
        if ($player === null) {
            throw new \Exception('applyEffectEvent should have a player');
        }

        return $player;
    }

    public function getAction(): Action
    {
        return $this->action;
    }

    public function getActionSupport(): ?LogParameterInterface
    {
        return $this->actionSupport;
    }

    public function getActionParameters(): array
    {
        return $this->actionParameters;
    }

    public function getActionResult(): ?ActionResult
    {
        return $this->actionResult;
    }

    public function setActionResult(?ActionResult $actionResult): self
    {
        $this->actionResult = $actionResult;

        return $this;
    }

    public function getModifiers(): ModifierCollection
    {
        $modifiers = $this->getAuthor()->getAllModifiers()->getEventModifiers($this)->getTargetModifiers(false);

        $actionSupport = $this->actionSupport;
        if ($actionSupport instanceof ModifierHolderInterface) {
            $modifiers = $modifiers->addModifiers($actionSupport->getAllModifiers()->getEventModifiers($this)->getTargetModifiers(true));
        }

        return $modifiers;
    }
}
