<?php

declare(strict_types=1);

namespace App\Jira\Resources;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Jira\DataProcessor\JiraDataProcessor;
use App\Jira\DataProvider\JiraItemDataProvider;
use App\Jira\DataTransformer\PropertyToCustomField;
use App\Jira\Enum\CustomField;
use App\Jira\Mapping\Attributes\JiraField;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Post(processor: JiraDataProcessor::class),
        new Get(
            requirements: ['id' => '.*'],
            provider: JiraItemDataProvider::class
        ),
    ],
    routePrefix: 'jira',
    normalizationContext: ['groups' => ['issue', 'priority']],
    denormalizationContext: []
)]
class UserStoryIssue extends Issue
{
    #[JiraField(transformer: PropertyToCustomField::class, options: ['field' => CustomField::UserStoryNumber])]
    #[Groups(['issue'])]
    public ?int $userStoryId = null;

    public function getMainClassId(): int
    {
        return $this->userStoryId;
    }
}
