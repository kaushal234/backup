<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SurveyCreation extends Constraint
{
    public string $message = 'toc.messages.errors.survey_creation';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
