<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\EmailValidator;

class EmailListValidator extends EmailValidator
{
    /**
     * @var string
     */
    final public const LIST_SEPARATOR = ';';

    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }
        $value = mb_trim(mb_trim((string) $value), self::LIST_SEPARATOR);
        $emails = explode(self::LIST_SEPARATOR, $value);
        foreach ($emails as $email) {
            parent::validate(mb_trim($email), $constraint);
        }
    }
}
