<?php

declare(strict_types=1);

namespace LegacyBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Parts\SparePartsRequest;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\DataProvider\ServiceBulletinItemProvider;
use LegacyBundle\Doctrine\ORM\Extension\ServiceBulletinSecurityAware;
use LegacyBundle\Entity\Directory\LocationById;
use LegacyBundle\Entity\Directory\PeopleById;
use LegacyBundle\Repository\ServiceBulletinRepository;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(repositoryClass: ServiceBulletinRepository::class, readOnly: true)]
#[ORM\Table(name: 'sb')]
#[ServiceBulletinSecurityAware]
#[ApiFilter(OrderFilter::class, properties: ['id', 'title', 'type', 'createdAt', 'ssdDecidedAt', 'category', 'status'])]
#[ApiFilter(SearchFilter::class, properties: ['id', 'title', 'description', 'type', 'lines.equipmentRecord.id'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['description' => 'partial', 'title' => 'partial', 'id' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiResource(
    operations: [
        new GetCollection(
            openapi: true,
        ),
        new Get(
            openapi: true,
            normalizationContext: ['groups' => [
                'legacy:service_bulletin:detail',
                'legacy:service_bulletin:item',
            ]],
            provider: ServiceBulletinItemProvider::class,
        ),
    ],
    normalizationContext: ['groups' => ['legacy:service_bulletin:detail']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
)]
class ServiceBulletin
{
    #[ORM\Column]
    #[Groups(['legacy:service_bulletin', 'legacy:service_bulletin:detail'])]
    public string $status;

    #[ORM\Column]
    #[Groups(['legacy:service_bulletin', 'legacy:service_bulletin:detail'])]
    public string $category;

    #[ORM\Column(name: 'category_reason', type: 'text', nullable: true)]
    public ?string $categoryReason = null;

    #[ORM\Column]
    #[Groups(['legacy:service_bulletin', 'legacy:service_bulletin:detail'])]
    public string $title;

    #[ORM\Column]
    #[Groups(['legacy:service_bulletin:detail'])]
    public string $type;

    #[ORM\Column]
    #[Groups(['legacy:service_bulletin:detail'])]
    public string $description;

    #[ORM\Column]
    #[Groups(['legacy:service_bulletin:detail'])]
    public string $confidential;

    #[ORM\Column(name: 'parent_id', type: 'integer')]
    public int $parentId;

    #[ORM\Column(name: 'ifactor', type: 'integer')]
    public int $importanceFactor;

    #[ORM\Column(name: 'labor', type: 'integer')]
    public int $laborHours;

    #[ORM\Column(name: 'nb_tech_needed', type: 'integer')]
    public int $numberOfTechniciansNeeded;

    #[ORM\Column(name: 'factory_part_availability_status', type: 'string', length: 30)]
    public string $factoryPartAvailabilityStatus;

    #[ORM\Column(name: 'dt_ssd_approval', type: 'nullable_zero_date')]
    public ?\DateTimeInterface $ssdApprovedAt = null;

    #[ORM\Column(name: 'dt_ssd_decision', type: 'nullable_zero_date')]
    #[Groups(['legacy:service_bulletin:detail'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    public ?\DateTimeInterface $ssdDecidedAt = null;

    #[ORM\Column(name: 'dt_implementation', type: 'nullable_zero_date')]
    public ?\DateTimeInterface $implementedAt = null;

    #[ORM\Column(name: 'dt_closed', type: 'nullable_zero_date')]
    public ?\DateTimeInterface $closedAt = null;

    #[ORM\ManyToOne(targetEntity: PeopleById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'poster_id', referencedColumnName: 'id', nullable: true)]
    public ?PeopleById $poster = null;

    #[ORM\ManyToOne(targetEntity: LocationById::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'bu_id', referencedColumnName: 'id', nullable: true)]
    public ?LocationById $factory = null;

    /** @var Collection<ServiceBulletinLine> */
    #[ORM\OneToMany(targetEntity: ServiceBulletinLine::class, mappedBy: 'serviceBulletin')]
    public Collection $lines;

    #[ORM\Column(name: 'dt', type: 'datetime')]
    #[Groups(['legacy:service_bulletin:detail'])]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    public \DateTimeInterface $createdAt;

    /** @var Collection<SparePartsRequest> */
    #[Groups(['legacy:service_bulletin'])]
    public Collection $sparePartsRequest;

    #[ORM\Column(name: 'parts_needed')]
    #[Groups(['legacy:service_bulletin:detail'])]
    private string $partsNeeded = '';

    #[ORM\Id]
    #[Groups(['legacy:service_bulletin:detail'])]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function __construct()
    {
        $this->sparePartsRequest = new ArrayCollection();
        $this->lines = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return list<array{status: string, equipmentRecord: array{id: int, serialNumber: string, model: string, type: string, customerName: string}}>
     */
    #[Groups(['legacy:service_bulletin:item'])]
    #[SerializedName('lines')]
    public function getEquipmentLines(): array
    {
        return array_values($this->lines->map(static fn (ServiceBulletinLine $line): array => [
            'status' => $line->status,
            'equipmentRecord' => [
                'id' => $line->equipmentRecord->getId(),
                'serialNumber' => $line->equipmentRecord->serialNumber,
                'model' => $line->equipmentRecord->model,
                'type' => $line->equipmentRecord->type,
                'customerName' => $line->equipmentRecord->customerName,
            ],
        ])->toArray());
    }

    #[Groups(['legacy:service_bulletin:detail'])]
    public function isPartsNeeded(): bool
    {
        return 'Y' === $this->partsNeeded;
    }
}
