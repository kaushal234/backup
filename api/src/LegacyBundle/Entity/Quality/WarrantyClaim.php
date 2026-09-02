<?php

declare(strict_types=1);

namespace LegacyBundle\Entity\Quality;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Entity\Directory\LocationByName;
use LegacyBundle\Entity\Directory\PeopleByUsername;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

#[ORM\Entity(readOnly: true)]
#[ORM\Table(name: 'warranty')]
#[ApiResource(
    operations: [
        new GetCollection(
            openapi: true,
            normalizationContext: ['groups' => ['legacy:warranty_claim']],
        ),
        new Get(
            openapi: true,
            normalizationContext: ['groups' => ['legacy:warranty_claim', 'legacy:warranty_claim:details', 'legacy:warranty_claim:parts', 'people']],
        ),
    ],
    normalizationContext: ['groups' => ['legacy:warranty_claim']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
)]
#[ApiFilter(SearchFilter::class, properties: ['status', 'type', 'equipmentModel', 'serialNumber', 'equipmentLocation', 'customerName'])]
#[ApiFilter(OrderFilter::class, properties: ['id', 'serialNumber', 'status', 'claimDate', 'type', 'equipmentModel', 'equipmentLocation'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['id' => 'exact', 'serialNumber' => 'partial', 'status' => 'exact', 'claimDate' => 'exact', 'type' => 'partial', 'equipmentModel' => 'partial', 'equipmentLocation' => 'partial'])]
class WarrantyClaim
{
    #[ORM\Column(name: 'warranty_status', type: 'string')]
    #[Groups(['legacy:warranty_claim'])]
    public string $status = '';

    #[ORM\Column(name: 'claim_date', type: 'nullable_zero_date', nullable: true)]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[Groups(['legacy:warranty_claim'])]
    public ?\DateTimeInterface $claimDate = null;

    #[ORM\Column(name: 'type', type: 'string')]
    #[Groups(['legacy:warranty_claim'])]
    public string $type = '';

    #[ORM\Column(name: 'model', type: 'string')]
    #[Groups(['legacy:warranty_claim'])]
    public string $equipmentModel = '';

    #[ORM\Column(name: 'serial_number', type: 'string', nullable: true)]
    #[Groups(['legacy:warranty_claim'])]
    public ?string $serialNumber = null;

    #[ORM\Column(name: 'hours', type: 'integer', nullable: true)]
    #[Groups(['legacy:warranty_claim:details'])]
    public ?int $equipmentHours = null;

    #[ORM\Column(name: 'customer_name', type: 'string', nullable: true)]
    #[Groups(['legacy:warranty_claim'])]
    public ?string $customerName = null;

    #[ORM\Column(name: 'claimant_details', type: 'text', nullable: true)]
    #[Groups(['legacy:warranty_claim:details'])]
    public ?string $claimantDetails = null;

    #[ORM\Column(name: 'equipment_location', type: 'text', nullable: true)]
    #[Groups(['legacy:warranty_claim'])]
    public ?string $equipmentLocation = null;

    #[ORM\ManyToOne(targetEntity: LocationByName::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'man_location', referencedColumnName: 'location', nullable: true)]
    public ?LocationByName $manufacturingLocation = null;

    #[ORM\ManyToOne(targetEntity: LocationByName::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'sales_org', referencedColumnName: 'location', nullable: true)]
    public ?LocationByName $salesOrganization = null;

    #[ORM\ManyToOne(targetEntity: PeopleByUsername::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'entered_by', referencedColumnName: 'username', nullable: true)]
    #[Groups(['legacy:warranty_claim:details'])]
    public ?PeopleByUsername $enteredBy = null;

    #[ORM\Column(name: 'warranty_details', type: 'text', nullable: true)]
    #[Groups(['legacy:warranty_claim:details'])]
    public ?string $details = null;

    #[ORM\Column(name: 'problem_desc', type: 'text', nullable: true)]
    #[Groups(['legacy:warranty_claim'])]
    public ?string $description = null;

    #[ORM\Column(name: 'extranet_prob_desc', type: 'text')]
    public string $extranetProblemDescription = '';

    #[ORM\Column(name: 'failure_code1', type: 'string')]
    public string $failureCode1 = '';

    #[ORM\Column(name: 'failure_code2', type: 'string')]
    public string $failureCode2 = '';

    #[ORM\Column(name: 'intervention', type: 'string')]
    public string $intervention = '';

    #[ORM\Column(name: 'est_man_hours', type: 'integer', nullable: true)]
    public ?int $estimatedManHours = null;

    #[ORM\ManyToOne(targetEntity: PeopleByUsername::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'prod_man_accept_user', referencedColumnName: 'username', nullable: true)]
    public ?PeopleByUsername $productionManagerAcceptUser = null;

    #[ORM\Column(name: 'prod_man_accept_date', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $productionManagerAcceptDate = null;

    #[ORM\Column(name: 'prod_man_comments', type: 'text')]
    public string $productionManagerComments = '';

    #[ORM\Column(name: 'parts_date_delivery', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $partsDeliveryDate = null;

    #[ORM\Column(name: 'parts_courier', type: 'text', nullable: true)]
    public ?string $partsCourier = null;

    #[ORM\Column(name: 'return_parts', type: 'string')]
    public string $returnParts = '';

    #[ORM\Column(name: 'service_accept_date', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $serviceAcceptDate = null;

    #[ORM\Column(name: 'service_date_delivery', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $serviceDeliveryDate = null;

    #[ORM\Column(name: 'service_comments', type: 'text')]
    public string $serviceComments = '';

    #[ORM\Column(name: 'service_ship_inst', type: 'text')]
    public string $serviceShippingInstructions = '';

    #[ORM\Column(name: 'technician', type: 'string')]
    public ?string $technician = null;

    #[ORM\Column(name: 'technician_cost_te', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    public ?string $technicianTravelExpenseCost = null;

    #[ORM\Column(name: 'technician_cost_labour', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    public ?string $technicianLabourCost = null;

    #[ORM\Column(name: 'parts_cost', type: 'decimal', precision: 10, scale: 2, nullable: true)]
    public ?string $partsCost = null;

    #[ORM\Column(name: 'note_cost', type: 'text')]
    public string $costNotes = '';

    #[ORM\Column(name: 'part_failing', type: 'string')]
    public string $criticalPartFailing = '';

    #[ORM\Column(name: 'part_return_address', type: 'text')]
    public string $partReturnAddress = '';

    #[ORM\Column(name: 'part_return_date', type: 'nullable_zero_date', nullable: true)]
    public ?\DateTimeInterface $partReturnDate = null;

    #[ORM\Column(name: 'parts_order_ref', type: 'text')]
    public string $partsOrderReference = '';

    /** @var Collection<WarrantyClaimPart> */
    #[ORM\OneToMany(targetEntity: WarrantyClaimPart::class, mappedBy: 'warrantyClaim')]
    #[Groups(['legacy:warranty_claim:parts'])]
    public Collection $parts;

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    public function __construct()
    {
        $this->parts = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }
}
