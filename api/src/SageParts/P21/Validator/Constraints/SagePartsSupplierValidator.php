<?php

declare(strict_types=1);

namespace App\SageParts\P21\Validator\Constraints;

use App\Entity\SupplierEntityInterface;
use App\SageParts\P21\Manager\SupplierManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SagePartsSupplierValidator extends ConstraintValidator
{
    public function __construct(
        private readonly SupplierManager $sagePartsSupplierManager,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SagePartsSupplier) {
            return;
        }

        $object = $this->context->getObject();

        if (!$object instanceof SupplierEntityInterface) {
            return;
        }

        if (null !== $value) {
            if (null === $this->sagePartsSupplierManager->findSupplier($value)) {
                $this->context->addViolation($constraint->message, ['{{ value }}' => $value]);
            }
        }
    }
}
