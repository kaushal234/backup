<?php

declare(strict_types=1);

namespace App\Jira\Resources;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Jira\DataProvider\JiraCollectionDataProvider;
use App\Jira\DataProvider\JiraItemDataProvider;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(provider: JiraCollectionDataProvider::class),
        new Get(requirements: ['id' => '.*'], provider: JiraItemDataProvider::class),
    ],
    routePrefix: 'jira',
    normalizationContext: ['groups' => ['priority']],
    denormalizationContext: [],
)]
class Priority
{
    #[ApiProperty(identifier: true)]
    #[Groups(['priority'])]
    public string $id;

    #[Groups(['priority'])]
    public string $name;

    #[Groups(['priority'])]
    public string $description;
}
