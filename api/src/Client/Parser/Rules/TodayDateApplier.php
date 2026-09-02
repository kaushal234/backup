<?php

declare(strict_types=1);

namespace App\Client\Parser\Rules;

use App\Client\Parser\SoapCallRuleApplierInterface;

class TodayDateApplier implements SoapCallRuleApplierInterface
{
    public function apply($value)
    {
        if (\is_string($value) && str_contains($value, date('Y-m-d'))) {
            return '{{ today }}';
        }

        return $value;
    }
}
