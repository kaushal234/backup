<?php

declare(strict_types=1);

namespace App\Client\Parser;

class ChainSoapCallRuleApplier implements SoapCallRuleApplierInterface
{
    /** @var iterable|SoapCallRuleApplierInterface[] */
    private readonly iterable $rules;

    public function __construct(iterable $rulesApplier)
    {
        $this->rules = $rulesApplier;
    }

    public function apply($value)
    {
        foreach ($this->rules as $rule) {
            if ($rule instanceof self) {
                continue;
            }
            $value = $rule->apply($value);
        }

        return $value;
    }
}
