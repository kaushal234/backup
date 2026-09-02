<?php

declare(strict_types=1);

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class LeadTime extends Constraint
{
    public $missingWeekMessage = "Family {{ family }}: You can't provide a description without providing a number of weeks";
    public $missingDescriptionMessage = "Family {{ family }}: You can't provide a number of weeks without providing a description";
}
