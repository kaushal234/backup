<?php

declare(strict_types=1);

namespace App\Jira\Resources;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Entity\Support\UnitOperationalStatus;
use App\Jira\DataProcessor\JiraDataProcessor;
use App\Jira\DataProvider\JiraCollectionDataProvider;
use App\Jira\DataProvider\JiraItemDataProvider;
use App\Jira\DataTransformer\HtmlToAdfTransformer;
use App\Jira\DataTransformer\ProductToCustomField;
use App\Jira\DataTransformer\PropertyToTracteasyCustomField;
use App\Jira\DataTransformer\PropertyToTracteasyCustomUrlField;
use App\Jira\DataTransformer\UnitOperationalStatusToCustomField;
use App\Jira\Enum\TracteasyCustomField;
use App\Jira\Mapping\Attributes\JiraField;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Post(name: 'jira_tracteasy_post_issue', processor: JiraDataProcessor::class),
        new Get(requirements: ['id' => '.*'], provider: JiraItemDataProvider::class),
        new GetCollection(provider: JiraCollectionDataProvider::class),
    ],
    routePrefix: 'jira_tracteasy',
    normalizationContext: ['groups' => ['issue', 'priority']],
    denormalizationContext: [],
    extraProperties: ['jira_context' => 'tracteasy'],
)]
class TracteasyHelpdeskIssue extends Issue
{
    #[JiraField(transformer: HtmlToAdfTransformer::class, options: ['field' => 'description'])]
    #[Groups(['issue'])]
    public string $description;

    #[Groups(['issue'])]
    public ?string $key = null;
    #[JiraField(transformer: PropertyToTracteasyCustomUrlField::class, options: ['field' => TracteasyCustomField::TechnicianOnCallId, 'route' => 'technician_on_call'])]
    #[Groups(['issue'])]
    public ?string $technicianOnCallId = null;

    #[JiraField(transformer: PropertyToTracteasyCustomField::class, options: ['field' => TracteasyCustomField::VehicleId])]
    #[Groups(['issue'])]
    public ?string $vehicleId = null;

    #[JiraField(
        transformer: UnitOperationalStatusToCustomField::class,
        options: ['field' => TracteasyCustomField::ServiceInterrupted]
    )]
    #[Groups(['issue'])]
    public ?UnitOperationalStatus $serviceInterrupted = null;

    #[JiraField(transformer: ProductToCustomField::class, options: ['field' => TracteasyCustomField::Product])]
    #[Groups(['issue'])]
    public ?string $product = null;

    public function getMainClassId(): int
    {
        return (int) $this->technicianOnCallId;
    }
}
