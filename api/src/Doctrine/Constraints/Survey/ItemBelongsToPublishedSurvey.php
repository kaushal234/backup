<?php

declare(strict_types=1);

namespace App\Doctrine\Constraints\Survey;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ItemBelongsToPublishedSurvey extends Constraint
{
    public string $message = 'The {{ class_name }} does not belong to the published survey ({{ survey_published_token }}).';

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
