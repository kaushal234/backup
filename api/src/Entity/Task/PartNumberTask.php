<?php

declare(strict_types=1);

namespace App\Entity\Task;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new Post(
            validationContext: ['groups' => ['Default', 'creation']],
        ),
        new Put(
            denormalizationContext: ['groups' => ['base_task:edit', 'task:edit', 'part_number_task:write']],
        ),
        new GetCollection(
            formats: ['jsonld', 'json', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            normalizationContext: ['groups' => ['base_task', 'task', 'part_number_task', 'module_light', 'people_public', 'location']]
        ),
    ],
    normalizationContext: ['groups' => ['base_task', 'task', 'part_number_task', 'task:item', 'module_light', 'people_public', 'people_photo', 'location_public', 'file']],
    denormalizationContext: ['groups' => ['base_task', 'task:write', 'part_number_task:write']],
)]
#[App\Loggable]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id' => 'partial',
    'description' => 'partial',
    'assignee.lastname' => 'partial',
    'location.name' => 'partial',
    'partNumber' => 'partial',
])]
#[ApiFilter(SearchFilter::class, properties: [
    'id' => 'exact',
    'assignee' => 'exact',
    'indiceFactor' => 'exact',
    'location' => 'exact',
    'status' => 'exact',
    'partNumber' => 'exact',
])]
#[ApiFilter(OrderFilter::class, properties: [
    'id',
    'description',
    'assignee.lastname',
    'dueDate',
    'createdAt',
    'startedAt',
    'status',
    'location.name',
    'indiceFactor',
    'partNumber',
])]
#[ApiFilter(DateFilter::class, properties: [
    'dueDate' => 'exact',
    'createdAt' => 'exact',
    'startedAt' => 'exact',
])]
class PartNumberTask extends Task
{
    final public const string PNT = 'PNT';
    #[ORM\Column(type: 'string', length: 100)]
    #[Groups(['task', 'part_number_task', 'part_number_task:write'])]
    private string $partNumber;

    public function getPartNumber(): string
    {
        return $this->partNumber;
    }

    public function setPartNumber(string $partNumber): void
    {
        $this->partNumber = $partNumber;
    }
}
