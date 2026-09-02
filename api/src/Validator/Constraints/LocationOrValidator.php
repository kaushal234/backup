<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use App\Entity\Directory\Location as DirectoryLocation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class LocationOrValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof LocationOr || !$value instanceof DirectoryLocation) {
            return;
        }

        if ($constraint->erpInLN && !$value->isErpInLN()) {
            $this->context->addViolation($constraint->errorMessageErpInBaan);

            return;
        }

        $capability = $value->getCapability();
        $requestedCapabilities = [];

        if ($constraint->sso) {
            $requestedCapabilities[] = 'SSO';
            if ($capability->isSso()) {
                return;
            }
        }

        if ($constraint->factory) {
            $requestedCapabilities[] = 'Factory';
            if ($capability->isFactory()) {
                return;
            }
        }

        if ($constraint->serviceHub) {
            $requestedCapabilities[] = 'Service Hub';
            if ($capability->isServiceHub()) {
                return;
            }
        }

        if ($constraint->sparePartsHub) {
            $requestedCapabilities[] = 'Spare Parts Hub';
            if ($capability->isSparePartsHub()) {
                return;
            }
        }

        if ($constraint->warehouse) {
            $requestedCapabilities[] = 'Warehouse';
            if ($capability->isWarehouse()) {
                return;
            }
        }

        if (!empty($requestedCapabilities)) {
            $this->context->addViolation($constraint->errorMessage, [
                '{{ capability }}' => implode(' or ', $requestedCapabilities),
            ]);
        }
    }
}
