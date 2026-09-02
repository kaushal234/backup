<?php

declare(strict_types=1);

namespace App\Entity\HumanResources;

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
use App\Entity\Country;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'events')]
#[ORM\Entity(repositoryClass: 'App\Repository\HumanResources\EventsRepository')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
        ),
        new Post(security: 'is_granted("FEATURE_HOLIDAYS_ADMIN")'),
        new Put(security: 'is_granted("FEATURE_HOLIDAYS_ADMIN")'),
        new Delete(security: 'is_granted("FEATURE_HOLIDAYS_ADMIN")'),
    ],
    normalizationContext: ['groups' => ['event', 'country_list']],
    denormalizationContext: ['groups' => ['event:write']],
)]
#[ApiFilter(OrderFilter::class, properties: ['name', 'startedAt', 'endedAt', 'country.name'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial', 'country.name' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: ['country'])]
#[ApiFilter(DateFilter::class, properties: ['startedAt', 'endedAt'])]
#[ApiFilter(ColumnsFilter::class)]
class Event
{
    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['event', 'event:write'])]
    public string $name;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['event', 'event:write'])]
    public \DateTimeInterface $startedAt;

    #[ORM\Column(type: 'datetime')]
    #[Assert\GreaterThanOrEqual(propertyPath: 'startedAt')]
    #[Groups(['event', 'event:write'])]
    public \DateTimeInterface $endedAt;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['event', 'event:write'])]
    public Country $country;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['event', 'event:write'])]
    public ?string $state = null;

    #[ORM\Column(type: 'boolean', nullable: false)]
    #[Groups(['event', 'event:write'])]
    public bool $dayOff = false;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['event'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
