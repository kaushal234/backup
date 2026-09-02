<?php

declare(strict_types=1);

namespace App\Tests\Jira\Entity;

use App\Jira\Mapping\Attributes\JiraField;
use App\Jira\Resources\CommentResourceInterface;

class JiraCommentResourceDummy implements CommentResourceInterface
{
    #[JiraField(transformer: UppercaseTransformerDummy::class, options: ['field' => 'body'])]
    public ?string $body = null;

    public ?string $author = null;

    public function __construct(
        private readonly string $issueKey,
    ) {
    }

    public function getIssueKey(): string
    {
        return $this->issueKey;
    }
}
