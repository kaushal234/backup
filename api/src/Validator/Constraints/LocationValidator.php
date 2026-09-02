<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Directory\Location as DirectoryLocation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LocationValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Location || !$value instanceof DirectoryLocation) {
            return;
        }

        $capability = $value->getCapability();

        if ($constraint->sso && !$capability->isSso()) {
            $this->context->addViolation($constraint->errorMessage, ['{{ capability }}' => 'SSO']);
        }

        if ($constraint->factory && !$capability->isFactory()) {
            $this->context->addViolation($constraint->errorMessage, ['{{ capability }}' => 'Factory']);
        }

        if ($constraint->serviceHub && !$capability->isServiceHub()) {
            $this->context->addViolation($constraint->errorMessage, ['{{ capability }}' => 'Service Hub']);
        }

        if ($constraint->sparePartsHub && !$capability->isSparePartsHub()) {
            $this->context->addViolation($constraint->errorMessage, ['{{ capability }}' => 'Spare Parts Hub']);
        }

        if ($constraint->warehouse && !$capability->isWarehouse()) {
            $this->context->addViolation($constraint->errorMessage, ['{{ capability }}' => 'Warehouse']);
        }

        if ($constraint->erpInLN && !$value->isErpInLN()) {
            $this->context->addViolation($constraint->errorMessageErpInBaan);
        }
    }
}
