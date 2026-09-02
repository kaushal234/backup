<?php

declare(strict_types=1);

namespace App\Jira\DataTransformer;

use App\Jira\Enum\CustomField;

class PropertyToCustomField implements JiraFieldDataTransformerInterface
{
    public function __invoke($value, array $options)
    {
        $field = $options['field'];
        $key = $field instanceof CustomField ? $options['field']->value : $options['field'];

        return [$key => $value];
    }
}
