<?php

declare(strict_types=1);

namespace App\ION\Validator\Constraints\MasterData\LogisticCodes;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Language extends Constraint
{
    public string $message = '{{ language }} is not a valid language.';
}
