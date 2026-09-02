<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ExtranetUserLinkedToEquipmentRecord extends Constraint
{
    public string $notAllowedMessage = 'toc.messages.errors.contact_not_allowed';
}
