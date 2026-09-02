<?php

declare(strict_types=1);

namespace App\Client\Parser;

class SoapCallParser
{
    private readonly ChainSoapCallRuleApplier $ruleApplier;

    public function __construct(ChainSoapCallRuleApplier $ruleApplier)
    {
        $this->ruleApplier = $ruleApplier;
    }

    public function parse($value)
    {
        if (!\is_array($value)) {
            return $this->ruleApplier->apply($value);
        }

        foreach ($value as $key => $data) {
            $value[$key] = $this->parse($data);
        }

        return $value;
    }
}
