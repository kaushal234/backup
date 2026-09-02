<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use ApiPlatform\Metadata\IriConverterInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ResourceExistsValidator extends ConstraintValidator
{
    private readonly IriConverterInterface $iriConverter;

    public function __construct(IriConverterInterface $iriConverter)
    {
        $this->iriConverter = $iriConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ResourceExists) {
            return;
        }

        if (null === $value) {
            return;
        }

        try {
            $this->iriConverter->getResourceFromIri($value);
        } catch (\Exception $exception) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
