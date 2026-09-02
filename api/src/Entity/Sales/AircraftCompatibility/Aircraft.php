<?php

declare(strict_types=1);

namespace App\Entity\Sales\AircraftCompatibility;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'unique_aircraft_name', columns: ['name'])]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new GetCollection(
            uriTemplate: '/aircraft_compatibilities/{id}/aircrafts',
            uriVariables: [
                'id' => new Link(fromProperty: 'aircrafts', fromClass: AircraftCompatibility::class),
            ],
            name: 'aircraft_by_compatibility'
        ),
        new Get(),
        new Put(security: "is_granted('FEATURE_EDIT_AIRCRAFT')  or is_granted('MOO_AC')"),
        new Delete(security: "is_granted('FEATURE_DELETE_AIRCRAFT') or is_granted('MOO_AC')"),
        new Post(security: "is_granted('FEATURE_CREATE_AIRCRAFT') or is_granted('MOO_AC')"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['aircraft', 'manufacturer']],
    denormalizationContext: ['groups' => ['aircraft:write']],
)]
#[ApiFilter(OrderFilter::class, properties: ['name', 'manufacturer.name'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
class Aircraft
{
    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(groups: ['aircraft', 'aircraft:write'])]
    public string $name;

    #[ORM\ManyToOne(targetEntity: Manufacturer::class)]
    #[Assert\NotNull]
    #[Groups(groups: ['aircraft', 'aircraft:write'])]
    public ?Manufacturer $manufacturer = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(groups: ['aircraft'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
