<?php

declare(strict_types=1);

namespace App\Jira\Mapping\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::IS_REPEATABLE)]
class JiraField
{
    public function __construct(
        public string $transformer,
        public ?array $options = []
    ) {
    }
}
