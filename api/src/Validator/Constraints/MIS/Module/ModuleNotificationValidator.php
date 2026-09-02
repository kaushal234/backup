<?php

declare(strict_types=1);

namespace App\Validator\Constraints\MIS\Module;

use App\Entity\Module\Module;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ModuleNotificationValidator extends ConstraintValidator
{
    /**
     * @param Module             $value
     * @param ModuleNotification $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof Module || 'DISABLED' === $value->status) {
            return;
        }

        if (false === $value->isNotifyOperationalOwner() && null === $value->getKeyUser()) {
            $this->context
                ->buildViolation($constraint->messageKeyUser)
                ->atPath('keyUser')
                ->addViolation();
        }

        if (false === $value->isNotifyKeyUser() && $value->getLocalKeyUsers()->isEmpty()) {
            $this->context
                ->buildViolation($constraint->messageLocalKeyUsers)
                ->atPath('localKeyUsers')
                ->addViolation();
        }
    }
}
