<?php

declare(strict_types=1);

namespace App\Jira\Resources;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Jira\DataProvider\JiraCollectionDataProvider;
use App\Jira\DataProvider\JiraItemDataProvider;

#[ApiResource(
    operations: [
        new GetCollection(provider: JiraCollectionDataProvider::class),
        new Get(requirements: ['id' => '.*'], provider: JiraItemDataProvider::class),
    ],
    routePrefix: 'jira_tracteasy',
    normalizationContext: ['groups' => ['issue_type']],
    denormalizationContext: [],
    extraProperties: ['jira_context' => 'tracteasy'],
)]
class TracteasyIssueType extends IssueType
{
    public const ISSUE_ISSUETYPE_ID = '10002'; // The only issuetype id we should use for tracteasy issues
}
