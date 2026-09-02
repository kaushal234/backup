<?php

declare(strict_types=1);

namespace App\Jira\DataTransformer;

use App\Jira\Enum\TracteasyCustomField;

class ProductToCustomField implements JiraFieldDataTransformerInterface
{
    public const MAPPING = [
        'EZTow' => '10074',
        'EZ Dolly' => '10075',
    ];

    public function __invoke($value, array $options): array
    {
        $field = $options['field'] instanceof TracteasyCustomField
            ? $options['field']->value
            : (string) $options['field'];

        if (!\is_string($value) || !\array_key_exists($value, self::MAPPING)) {
            throw new \InvalidArgumentException(\sprintf('Unmapped product "%s" for Jira Tracteasy field', (string) $value));
        }

        return [
            $field => [
                'id' => self::MAPPING[$value],
            ],
        ];
    }
}
