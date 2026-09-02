<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(openapi: true),
        new Get(),
    ],
    normalizationContext: ['groups' => ['unit_operational_status_detail']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')"
)]
#[ApiFilter(SearchFilter::class, properties: [
    'name',
])]
#[ApiFilter(OrderFilter::class, properties: [
    'name',
])]
#[ORM\Table(name: 'unit_operational_statuses')]
class UnitOperationalStatus
{
    public const MCF = 'MCF';
    public const NMC = 'NMC';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ApiProperty(identifier: false)]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['unit_operational_status_detail'])]
    private int $id;

    #[ApiProperty(identifier: true)]
    #[ORM\Column(name: 'name', type: 'string', length: 3, unique: true, nullable: false)]
    #[Groups(['unit_operational_status_detail'])]
    private string $name;

    #[ORM\Column(name: 'description', type: 'string', length: 50, nullable: false)]
    #[Groups(['unit_operational_status_detail'])]
    private string $description;

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
