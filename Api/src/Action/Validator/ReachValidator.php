<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ReachValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        if ($constraint->player) {
            $targetPlayer = $value->getParameter();
            if ($targetPlayer === $value->getPlayer() ||
                $targetPlayer->getPlace() !== $value->getPlayer()->getPlace()
            ) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        } else {
            if (!$value->getPlayer()->canReachEquipment($value->getParameter())) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
