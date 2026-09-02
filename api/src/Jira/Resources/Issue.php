<?php

declare(strict_types=1);

namespace App\Jira\Resources;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Jira\DataProvider\JiraItemDataProvider;
use App\Jira\DataTransformer\ObjectToJiraId;
use App\Jira\DataTransformer\TextToDocumentField;
use App\Jira\Mapping\Attributes\JiraField;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.*'], provider: JiraItemDataProvider::class),
    ],
    routePrefix: 'jira',
    normalizationContext: ['groups' => ['issue', 'priority']],
    denormalizationContext: [],
)]
abstract class Issue
{
    #[ApiProperty(identifier: true)]
    #[Groups(['issue'])]
    public ?string $id = null;

    #[Groups(['issue'])]
    public ?\DateTimeInterface $startDueDate = null;

    #[Groups(['issue'])]
    public ?\DateTimeInterface $endDueDate = null;

    #[Groups(['issue'])]
    public string $status;

    #[Groups(['issue'])]
    public string $summary;

    #[Groups(['issue'])]
    #[JiraField(transformer: TextToDocumentField::class, options: ['field' => 'description'])]
    public string $description;

    #[JiraField(transformer: ObjectToJiraId::class, options: ['field' => 'issuetype'])]
    public IssueType $type;

    #[JiraField(transformer: ObjectToJiraId::class, options: ['field' => 'priority'])]
    #[Groups(['issue'])]
    public Priority $priority;

    abstract public function getMainClassId(): int;
}
