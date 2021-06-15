<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FullHullValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof FullHull) {
            throw new UnexpectedTypeException($constraint, Reach::class);
        }

        $daedalus = $value->getPlayer()->getDaedalus();

        $maxHullPoint = $daedalus->getGameConfig()->getDaedalusConfig()->getMaxHull();

        if ($daedalus->getHull() === $maxHullPoint) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
