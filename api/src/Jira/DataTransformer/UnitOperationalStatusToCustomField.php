<?php

declare(strict_types=1);

namespace App\Jira\DataTransformer;

use App\Entity\Support\UnitOperationalStatus;
use App\Jira\Enum\TracteasyCustomField;

class UnitOperationalStatusToCustomField implements JiraFieldDataTransformerInterface
{
    public const MAPPING = [
        'MCF' => '10085',
        'MCP' => '10084',
        'NMC' => '10083',
    ];

    public function __invoke($value, array $options): array
    {
        $field = $options['field'] instanceof TracteasyCustomField
            ? $options['field']->value
            : (string) $options['field'];

        if (!$value instanceof UnitOperationalStatus) {
            return [$field => null];
        }

        return [
            $field => [
                'id' => self::MAPPING[$value->getName()],
            ],
        ];
    }
}
