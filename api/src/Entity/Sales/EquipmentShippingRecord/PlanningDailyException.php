<?php

declare(strict_types=1);

namespace App\Entity\Sales\EquipmentShippingRecord;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            denormalizationContext: ['groups' => ['planning_daily_limit:add']],
            security: "is_granted('FEATURE_PLANNING_DAILY_LIMIT_WRITE')"
        ),
        new Put(security: "is_granted('FEATURE_PLANNING_DAILY_LIMIT_WRITE')"),
        new Delete(security: "is_granted('FEATURE_PLANNING_DAILY_LIMIT_WRITE')"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['planning_daily_limit', 'location_public']],
    denormalizationContext: ['groups' => ['planning_daily_limit:edit']]
)]
#[ORM\Entity]
#[ORM\Table(name: 'planning_daily_exception')]
#[ORM\UniqueConstraint(name: 'uniq_factory_date', columns: ['factory_id', 'date'])]
#[UniqueEntity(fields: ['factory', 'date'], message: 'An exception already exists for this factory and date')]
#[ApiFilter(SearchFilter::class, properties: ['factory' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['date'])]
class PlanningDailyException
{
    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[ValidLocation(factory: true)]
    #[Groups(['planning_daily_limit', 'planning_daily_limit:add'])]
    public Location $factory;

    #[ORM\Column(type: 'date')]
    #[Groups(['planning_daily_limit', 'planning_daily_limit:add', 'planning_daily_limit:edit'])]
    public \DateTimeInterface $date;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['planning_daily_limit', 'planning_daily_limit:add', 'planning_daily_limit:edit'])]
    public ?string $comment = null;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    public \DateTime $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[Gedmo\Blameable(on: 'create')]
    public People $createdBy;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['planning_daily_limit'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
