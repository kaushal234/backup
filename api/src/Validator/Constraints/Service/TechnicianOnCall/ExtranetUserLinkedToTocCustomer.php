<?php

declare(strict_types=1);

namespace App\Validator\Constraints\Service\TechnicianOnCall;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ExtranetUserLinkedToTocCustomer extends Constraint
{
    public string $notAllowedMessage = 'toc.messages.errors.main_contact_not_allowed';
}
