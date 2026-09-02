<?php

declare(strict_types=1);

namespace App\Client\Parser\Rules;

use App\Client\Parser\SoapCallRuleApplierInterface;

class AtomFormattedDateApplier implements SoapCallRuleApplierInterface
{
    public function apply($value)
    {
        if (\is_string($value) && preg_match('/\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2]\d|3[0-1])T[0-2]\d:[0-5]\d:[0-5]\d[+-][0-2]\d:[0-5]\d/', $value)) {
            preg_match('/\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2]\d|3[0-1])/', $value, $result);

            return $result[0];
        }

        return $value;
    }
}
