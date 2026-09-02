<?php

declare(strict_types=1);

namespace App\Dto;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\DataProvider\WorkflowDataProvider;

#[ApiResource(
    operations: [
        new Get(requirements: ['id' => '.+'], provider: WorkflowDataProvider::class),
    ],
)]
class Workflow
{
    #[ApiProperty(identifier: true)]
    private readonly string $resource;

    #[ApiProperty(identifier: true)]
    private ?string $name = null;

    private array $availableStatuses = [];

    public function __construct(string $resource, ?string $name = null)
    {
        $this->resource = $resource;
        $this->name = $name;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getAvailableStatuses(): array
    {
        return $this->availableStatuses;
    }

    public function setAvailableStatuses(array $availableStatuses): self
    {
        $this->availableStatuses = $availableStatuses;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
