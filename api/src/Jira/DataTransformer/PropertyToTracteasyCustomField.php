<?php

declare(strict_types=1);

namespace App\Jira\DataTransformer;

use App\Jira\Enum\TracteasyCustomField;

class PropertyToTracteasyCustomField implements JiraFieldDataTransformerInterface
{
    public function __invoke($value, array $options)
    {
        $field = $options['field'];
        $key = $field instanceof TracteasyCustomField ? $options['field']->value : $options['field'];

        return [$key => $value];
    }
}
