<?php

declare(strict_types=1);

namespace App\Jira\Resources;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Jira\DataProcessor\JiraDataProcessor;
use App\Jira\DataTransformer\HtmlToAdfTransformer;
use App\Jira\DataTransformer\PropertyToJiraField;
use App\Jira\Mapping\Attributes\JiraField;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Post(name: 'jira_tracteasy_post_comment', processor: JiraDataProcessor::class),
    ],
    routePrefix: 'jira_tracteasy',
    normalizationContext: ['groups' => ['issue']],
    denormalizationContext: [],
    extraProperties: ['jira_context' => 'tracteasy'],
)]
class JiraIssueComment implements CommentResourceInterface
{
    #[Groups(['issue'])]
    public ?string $id = null;

    #[JiraField(transformer: HtmlToAdfTransformer::class, options: ['field' => 'body'])]
    #[Groups(['issue'])]
    public ?string $body = null;

    #[JiraField(transformer: PropertyToJiraField::class, options: ['field' => 'jsdPublic'])]
    #[Groups(['issue'])]
    public bool $public = false;

    public function __construct(
        private readonly string $issueKey,
    ) {
    }

    public function getIssueKey(): string
    {
        return $this->issueKey;
    }
}
