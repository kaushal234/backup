<?php

declare(strict_types=1);

namespace App\Entity\Sales\AircraftCompatibility;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'unique_manufacturer_name', columns: ['name'])]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['manufacturer']],
)]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
class Manufacturer
{
    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(groups: ['manufacturer'])]
    public string $name;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(groups: ['manufacturer'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
