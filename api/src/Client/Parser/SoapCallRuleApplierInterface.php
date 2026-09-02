<?php

declare(strict_types=1);

namespace App\Client\Parser;

interface SoapCallRuleApplierInterface
{
    public function apply($value);
}
