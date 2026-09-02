<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class ValidExtranetUser extends Constraint
{
    public string $disabledMessage = 'toc.messages.errors.contact_disabled';
}
