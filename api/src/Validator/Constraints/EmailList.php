<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Email;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class EmailList extends Email
{
}
