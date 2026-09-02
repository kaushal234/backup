<?php

declare(strict_types=1);

namespace App\Entity\Service;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Common\Airport;
use App\Entity\Directory\People;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'], message: 'This Service Area name is already used')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['service_area', 'people_public', 'airport_list', 'iata_code']]),
        new Put(security: "is_granted('SERVICE_AREA_WRITE_VOTER')"),
        new Get(),
        new Post(security: "is_granted('SERVICE_AREA_WRITE_VOTER')"),
        new Delete(security: "is_granted('SERVICE_AREA_WRITE_VOTER')"),
    ],
    normalizationContext: ['groups' => ['service_area', 'people_public', 'airport_list', 'iata_code']],
    denormalizationContext: ['groups' => ['service_area_write']],
)]
#[ORM\Table(name: 'service_areas')]
#[ApiFilter(OrderFilter::class, properties: ['name', 'representative.lastname'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'name' => 'partial',
    'representative.lastname' => 'partial',
    'representative.firstname' => 'partial',
    'airports.code' => 'partial',
    'airports.cityName' => 'partial',
])]
#[ApiFilter(SearchFilter::class, properties: [
    'airports' => 'exact',
    'airports.country' => 'exact',
    'airports.code' => 'exact',
    'representative' => 'exact',
])]
class ServiceArea
{
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['service_area', 'service_area_write', 'airport_service_areas'])]
    public string $name;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['service_area', 'service_area_write'])]
    public People $representative;
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['service_area'])]
    private int $id;

    /**
     * @var Collection<int, Airport>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Common\Airport', inversedBy: 'serviceAreas')]
    #[ORM\JoinTable(name: 'service_areas_airports')]
    #[Assert\Count(min: 1, minMessage: 'A Service Area must contain at least one airport')]
    #[Groups(['service_area', 'service_area_write'])]
    private Collection $airports;

    public function __construct()
    {
        $this->airports = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Airport>
     */
    public function getAirports(): Collection
    {
        return $this->airports;
    }

    public function addAirport(Airport $airport): self
    {
        if (!$this->airports->contains($airport)) {
            $this->airports->add($airport);
        }

        return $this;
    }

    public function removeAirport(Airport $airport): self
    {
        $this->airports->removeElement($airport);

        return $this;
    }
}
