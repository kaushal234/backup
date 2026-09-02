<?php

declare(strict_types=1);

namespace App\Tests\Jira\Entity;

use App\Jira\Mapping\Attributes\JiraField;

class JiraIssueResourceDummy
{
    #[JiraField(transformer: UppercaseTransformerDummy::class, options: ['field' => 'summary'])]
    public ?string $summary = null;

    public ?string $description = null;

    public function __construct(
        private readonly int $mainClassId,
    ) {
    }

    public function getMainClassId(): int
    {
        return $this->mainClassId;
    }
}
