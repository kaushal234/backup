<?php

declare(strict_types=1);

namespace App\Doctrine\Constraints\Survey;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class AnswerValueInRatingType extends Constraint
{
    public string $message = 'Answer value {{ value }} should be between {{ rating_min }} and {{ rating_max }}';

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
