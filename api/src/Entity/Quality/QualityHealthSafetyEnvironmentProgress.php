<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    uriTemplate: '/qhse_progress',
    operations: [
        new GetCollection(),
        new Get(uriTemplate: '/qhse_progress/{id}'),
        new Post(security: "is_granted('FEATURE_QHSE_WRITE')"),
        new Put(uriTemplate: '/qhse_progress/{id}', security: "is_granted('FEATURE_QHSE_WRITE')"),
        new Delete(uriTemplate: '/qhse_progress/{id}', security: "is_granted('FEATURE_QHSE_WRITE')"),
    ],
    routePrefix: 'quality',
    normalizationContext: ['groups' => ['qhse', 'people_public', 'location_public']],
    denormalizationContext: ['groups' => ['qhse:write']],
)]
#[ORM\Table(name: 'qhse')]
#[ORM\UniqueConstraint(name: 'unique_rating_per_month_per_location', columns: ['location_id', 'date'])]
#[ApiFilter(OrderFilter::class, properties: ['date' => 'DESC', 'id' => 'DESC'])]
#[ApiFilter(DateFilter::class, properties: ['date'])]
#[ApiFilter(BooleanFilter::class, properties: ['location.capability.factory'])]
#[ApiFilter(SearchFilter::class, properties: ['location' => 'exact'])]
#[App\Loggable(owner: 'location')]
class QualityHealthSafetyEnvironmentProgress
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['qhse', 'qhse:write'])]
    public Location $location;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 100)]
    #[Groups(['qhse', 'qhse:write'])]
    public int $rating;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['qhse'])]
    #[Gedmo\Blameable(on: 'update')]
    public People $poster;
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['qhse'])]
    private int $id;

    #[ORM\Column(type: 'date')]
    #[Assert\NotNull]
    #[Groups(['qhse', 'qhse:write'])]
    private \DateTimeInterface $date;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTime $date)
    {
        $this->date = $date->modify('first day of this month');

        return $this;
    }
}
