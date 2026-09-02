<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service;

use Doctrine\DBAL\Connection;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ServiceBulletinExistValidator extends ConstraintValidator
{
    public function __construct(
        private readonly Connection $legacyConnection,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        $stmt = $this->legacyConnection->executeQuery(\sprintf('SELECT * FROM sb WHERE id = %d', $value));
        $result = $stmt->fetchAllAssociative();

        if ([] === $result) {
            $this->context
                ->buildViolation($constraint->message)
                ->addViolation()
            ;
        }
    }
}
