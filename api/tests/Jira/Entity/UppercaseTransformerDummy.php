<?php

declare(strict_types=1);

namespace App\Tests\Jira\Entity;

use App\Jira\DataTransformer\JiraFieldDataTransformerInterface;

class UppercaseTransformerDummy implements JiraFieldDataTransformerInterface
{
    public function __invoke($value, array $options)
    {
        return [$options['field'] => mb_strtoupper((string) $value)];
    }
}
