<?php

namespace Mush\Test\Action\Validator;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Action\Validator\HasAction;
use Mush\Action\Validator\HasActionValidator;
use Mush\Equipment\Entity\Config\ItemConfig;
use Mush\Equipment\Entity\Equipment;
use Mush\Equipment\Entity\Item;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class ParameterHasActionValidatorTest extends TestCase
{
    private HasActionValidator $validator;
    private HasAction $constraint;

    /** @var GearToolServiceInterface|Mockery\Mock */
    private GearToolServiceInterface $gearToolService;

    /**
     * @before
     */
    public function before()
    {
        $this->gearToolService = Mockery::mock(GearToolServiceInterface::class);

        $this->validator = new HasActionValidator($this->gearToolService);
        $this->constraint = new HasAction();
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testValid()
    {
        $actionEntity = new Action();

        $itemConfig = new ItemConfig();
        $itemConfig->setActions(new ArrayCollection([$actionEntity]));

        $gameItem = new Item();
        $gameItem->setConfig($itemConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getAction' => $actionEntity,
                'getParameter' => $gameItem,
                'getPlayer' => new Player(),
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValid()
    {
        $itemConfig = new ItemConfig();
        $itemConfig->setActions(new ArrayCollection([]));

        $gameItem = new Item();
        $gameItem->setConfig($itemConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getAction' => new Action(),
                'getParameter' => $gameItem,
                'getActionName' => 'some_name',
                'getPlayer' => new Player(),
            ])
        ;

        $this->gearToolService->shouldReceive('getUsedTool')->andReturn(null);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testValidTool()
    {
        $itemConfig = new ItemConfig();
        $itemConfig->setActions(new ArrayCollection([]));

        $gameItem = new Item();
        $gameItem->setConfig($itemConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getAction' => new Action(),
                'getParameter' => $gameItem,
                'getActionName' => 'some_name',
                'getPlayer' => new Player(),
            ])
        ;

        $this->gearToolService->shouldReceive('getUsedTool')->andReturn(new Equipment());

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);

        $this->assertTrue(true);
    }

    public function testNotValidTool()
    {
        $itemConfig = new ItemConfig();
        $itemConfig->setActions(new ArrayCollection([]));

        $gameItem = new Item();
        $gameItem->setConfig($itemConfig);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getAction' => new Action(),
                'getParameter' => $gameItem,
                'getActionName' => 'some_name',
                'getPlayer' => new Player(),
            ])
        ;

        $this->gearToolService->shouldReceive('getUsedTool')->andReturn(null);

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    protected function initValidator(?string $expectedMessage = null)
    {
        $builder = Mockery::mock(ConstraintViolationBuilder::class);
        $context = Mockery::mock(ExecutionContext::class);

        if ($expectedMessage) {
            $builder->shouldReceive('addViolation')->andReturn($builder)->once();
            $context->shouldReceive('buildViolation')->with($expectedMessage)->andReturn($builder)->once();
        } else {
            $context->shouldReceive('buildViolation')->never();
        }

        /* @var ExecutionContext $context */
        $this->validator->initialize($context);

        return $this->validator;
    }
}
