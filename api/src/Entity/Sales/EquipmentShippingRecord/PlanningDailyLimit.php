<?php

declare(strict_types=1);

namespace App\Entity\Sales\EquipmentShippingRecord;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\Location;
use App\Repository\Sales\PlanningDailyLimitRepository;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: PlanningDailyLimitRepository::class)]
#[UniqueEntity(fields: ['factory'], message: 'The limit of this factory has been already set')]
#[ORM\UniqueConstraint(name: 'unique_factory', columns: ['factory_id'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(security: "is_granted('FEATURE_PLANNING_DAILY_LIMIT_READ')"),
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
#[ApiFilter(SearchFilter::class, properties: ['factory' => 'exact'])]
class PlanningDailyLimit
{
    #[ORM\OneToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[Groups(['planning_daily_limit', 'planning_daily_limit:add'])]
    #[ValidLocation(factory: true)]
    public Location $factory;

    #[ORM\Column(type: 'integer')]
    #[Groups(['planning_daily_limit', 'planning_daily_limit:edit', 'planning_daily_limit:add'])]
    public int $days;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['planning_daily_limit', 'planning_daily_limit:edit', 'planning_daily_limit:add'])]
    public ?string $comment = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['planning_daily_limit'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
