<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class EmailValidationException extends AbstractValidationException
{
    private readonly ConstraintViolationListInterface $violations;
    private ?string $subject = null;

    public function __construct(ConstraintViolationListInterface $violations, ?string $subject = null)
    {
        parent::__construct('Validation of this email has failed.', Response::HTTP_BAD_REQUEST);
        $this->violations = $violations;
        $this->subject = $subject;
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }
}
