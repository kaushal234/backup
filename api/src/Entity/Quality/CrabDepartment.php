<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Directory\Position;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'quality',
    normalizationContext: ['groups' => ['crab_departement']],
    security: "is_granted('ACCESS_PEOPLE')",
)]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'exact'])]

class CrabDepartment
{
    #[ORM\Column(type: 'string')]
    #[Groups('crab_departement')]
    public string $name;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Position')]
    #[ORM\JoinColumn(nullable: false)]
    public Position $derogationPosition;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups('crab_departement')]
    private $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
