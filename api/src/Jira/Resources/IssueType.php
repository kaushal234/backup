<?php

declare(strict_types=1);

namespace App\Jira\Resources;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Jira\DataProvider\JiraCollectionDataProvider;
use App\Jira\DataProvider\JiraItemDataProvider;
use App\Jira\Filter\IssueTypeProjectIdFilter;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(provider: JiraCollectionDataProvider::class),
        new Get(requirements: ['id' => '.*'], provider: JiraItemDataProvider::class),
    ],
    routePrefix: 'jira',
    normalizationContext: ['groups' => ['issue_type']],
    denormalizationContext: [],
)]
#[ApiFilter(IssueTypeProjectIdFilter::class)]
class IssueType
{
    #[ApiProperty(identifier: true)]
    #[Groups(['issue_type'])]
    public string $id;

    #[Groups(['issue_type'])]
    public string $name;

    #[Groups(['issue_type'])]
    public string $description;
}
