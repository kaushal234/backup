<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\Location;
use App\Validator\Constraints\LocationOr as ValidOrLocation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['location', 'defaultCosts'], message: 'Default costs already defined for this location')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_NON_QUALITY_COSTS_ADMIN')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_NON_QUALITY_COSTS_ADMIN')"),
    ],
    routePrefix: 'quality',
    normalizationContext: ['groups' => ['non_conformity_costs', 'location_public']],
    denormalizationContext: ['groups' => ['non_conformity_costs:write']],
)]
#[ORM\Table(name: 'non_quality_costs')]
#[ORM\UniqueConstraint(name: 'unique_non_quality_cost_per_location', columns: ['location_id', 'default_costs'])]
#[ApiFilter(OrderFilter::class, properties: ['location.name'])]
class NonQualityCost
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['non_conformity_costs', 'non_conformity_costs:write'])]
    #[ValidOrLocation(sso: true, factory: true)]
    public Location $location;

    #[ORM\Column(type: 'integer')]
    #[Assert\GreaterThanOrEqual(0)]
    #[Groups(['non_conformity_costs', 'non_conformity_costs:write'])]
    public int $defaultCosts;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
