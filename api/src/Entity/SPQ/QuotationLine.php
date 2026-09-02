<?php

declare(strict_types=1);

namespace App\Entity\SPQ;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Serializer\Filter\ContextFilter;
use App\Serializer\Filter\PropertyFilter;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['position', 'quotation'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['quotation_line', 'people_public', 'expose_legacy']]),
        new Get(),
    ],
    routePrefix: 'parts',
    normalizationContext: ['groups' => ['quotation_line:detail', 'people_public', 'expose_legacy']],
    denormalizationContext: ['groups' => []]
)]
#[ORM\Table(name: 'spq_quotation_lines')]
#[ApiFilter(OrderFilter::class, properties: ['createdAt' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['status', 'quotation', 'partNumber', 'quotation.sph', 'quotation.status', 'quotation.poster', 'quotation.quoter', 'quotation.baanCustomerNumber'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'quotation.closedAt' => 'exclude_null'])]
#[ApiFilter(GroupFilter::class, arguments: ['overrideDefaultGroups' => true, 'parameterName' => 'normalization_groups', 'whitelist' => ['quotation_lines:reports', 'people_list', 'location_public']])]
#[ApiFilter(BooleanFilter::class, properties: ['quotation.payableService'])]
#[ApiFilter(PropertyFilter::class, arguments: ['whitelist' => ['id', 'status', 'partNumber', 'description', 'unitBasePrice', 'currency', 'quantity', 'salesUnit', 'discount', 'commission', 'totalPrice', 'createdAt', 'quotation' => ['id', 'status', 'baanCustomerNumber', 'baanCustomerName', 'sph' => ['name', 'erp'], 'customerPurchaseOrder', 'baanSalesOrder', 'poster' => ['firstname', 'lastname'], 'quoter' => ['firstname', 'lastname'], 'reason', 'payableService']]])]
#[ApiFilter(ContextFilter::class)]
class QuotationLine
{
    /**
     * @var string
     */
    final public const PENDING = 'PENDING';
    /**
     * @var string
     */
    final public const QUOTED = 'QUOTED';
    /**
     * @var string
     */
    final public const SOLD = 'SOLD';
    /**
     * @var string
     */
    final public const CANCELLED = 'CANCELLED';
    /**
     * @var string
     */
    final public const LOST = 'LOST';
    /**
     * @var string
     */
    final public const DELETED = 'DELETED';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail', 'quotation_lines:reports'])]
    private ?int $id = null;

    #[ORM\Column(name: 'position', type: 'integer')]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail'])]
    #[Assert\NotNull]
    #[Assert\Type('integer')]
    #[Assert\Range(min: 0)]
    private int $position;

    #[ORM\Column(name: 'status', type: 'string', length: 25)]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail', 'quotation_lines:reports'])]
    private string $status = self::PENDING;

    #[ORM\Column(name: 'part_number', type: 'string', length: 25)]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private string $partNumber;

    #[ORM\Column(name: 'displayed_part_number', type: 'string', length: 255, nullable: true)]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail'])]
    private ?string $displayedPartNumber = null;

    #[ORM\Column(name: 'description', type: 'string', length: 255)]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private string $description;

    #[ORM\Column(name: 'unit_base_price', type: 'float')]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull]
    #[Assert\Type('float')]
    #[Assert\Range(min: 0)]
    private float $unitBasePrice = 0.0;

    private float $extendedUnitPrice = 0.0;

    #[ORM\Column(name: 'total_price', type: 'float')]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull]
    #[Assert\Type('float')]
    #[Assert\Range(min: 0)]
    private float $totalPrice = 0.0;

    #[ORM\Column(name: 'currency', type: 'string', length: 10)]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['EUR', 'USD', 'RMB', 'HKD', 'AED'])]
    private string $currency;

    #[ORM\Column(name: 'quantity', type: 'float')]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull]
    #[Assert\Type('float')]
    #[Assert\Range(min: 0)]
    private float $quantity = 0.0;

    #[ORM\Column(name: 'sales_unit', type: 'string', length: 3)]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull]
    #[Assert\Type('string')]
    #[Assert\Length(max: 3)]
    private string $salesUnit;

    #[ORM\Column(name: 'discount', type: 'float')]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull]
    #[Assert\Type('float')]
    #[Assert\Range(min: 0, max: 100)]
    private float $discount = 0.0;

    #[ORM\Column(name: 'commission', type: 'float')]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull]
    #[Assert\Type('float')]
    #[Assert\Range(min: 0, max: 99)]
    private float $commission = 0.0;

    #[ORM\Column(name: 'leadtime', type: 'smallint')]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail'])]
    #[Assert\NotNull]
    #[Assert\Type('integer')]
    #[Assert\Range(min: 0)]
    private int $leadtime;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation_lines:reports'])]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: true)]
    #[Groups(['quotation_line', 'quotation_line:detail'])]
    #[Gedmo\Timestampable(on: 'update')]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(name: 'quoted_at', type: 'datetime', nullable: true)]
    #[Groups(['quotation_line', 'quotation_line:detail'])]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: self::QUOTED)]
    private ?\DateTimeInterface $quotedAt = null;

    #[ORM\Column(name: 'closed_at', type: 'datetime', nullable: true)]
    #[Groups(['quotation_line', 'quotation_line:detail'])]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: [self::LOST, self::CANCELLED, self::SOLD])]
    private ?\DateTimeInterface $closedAt = null;

    #[ORM\Column(name: 'comment', type: 'text')]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail'])]
    private ?string $comment = '';

    #[ORM\ManyToOne(targetEntity: 'App\Entity\SPQ\Quotation', inversedBy: 'quotationLines')]
    #[ORM\JoinColumn(name: 'quotation_id', nullable: false)]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull]
    private ?Quotation $quotation = null;

    #[ORM\Column(name: 'tax', type: 'float')]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail'])]
    #[Assert\NotNull]
    #[Assert\Type('float')]
    #[Assert\Range(min: 0, max: 100)]
    private float $tax = 0.0;

    #[ORM\Column(name: 'weight', type: 'float')]
    #[Groups(['quotation_line', 'quotation_line:detail', 'quotation:detail'])]
    #[Assert\NotNull]
    #[Assert\Type('float')]
    #[Assert\Range(min: 0)]
    private float $weight = 0.0;

    #[ORM\Column(name: 'country_of_origin', type: 'string', length: 3, nullable: true)]
    #[Groups(['quotation_line:detail', 'quotation:detail'])]
    #[Assert\Length(max: 3)]
    private ?string $countryOfOrigin = null;

    #[ORM\Column(name: 'commodity_code', type: 'string', length: 10, nullable: true)]
    #[Groups(['quotation_line:detail', 'quotation:detail'])]
    #[Assert\Length(max: 10)]
    private ?string $commodityCode = null;

    #[ORM\Column(name: 'item_type', type: 'string', length: 14)]
    #[Groups(['quotation_line:detail', 'quotation:detail'])]
    #[Assert\NotNull]
    #[Assert\Length(max: 14)]
    private string $itemType;

    #[ORM\Column(name: 'deleted_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function reset(): self
    {
        if (0 !== $this->id) {
            $this->id = null;
            $this->status = self::PENDING;
            $this->createdAt = null;
            $this->updatedAt = null;
            $this->quotedAt = null;
            $this->closedAt = null;
            $this->quotation = null;
        }

        return $this;
    }

    public function getQuotation(): Quotation
    {
        return $this->quotation;
    }

    public function setQuotation(?Quotation $quotation): self
    {
        $this->quotation = $quotation;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setPartNumber(string $partNumber): self
    {
        $this->partNumber = $partNumber;

        return $this;
    }

    public function getPartNumber(): string
    {
        return $this->partNumber;
    }

    public function setDisplayedPartNumber(?string $displayedPartNumber): self
    {
        $this->displayedPartNumber = $displayedPartNumber;

        return $this;
    }

    public function getDisplayedPartNumber(): ?string
    {
        return $this->displayedPartNumber;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setUnitBasePrice(float $unitBasePrice): self
    {
        $this->unitBasePrice = $unitBasePrice;

        return $this;
    }

    public function getUnitBasePrice(): float
    {
        return $this->unitBasePrice;
    }

    public function getExtendedUnitPrice(): float
    {
        return $this->extendedUnitPrice;
    }

    /**
     * @return $this
     */
    public function setExtendedUnitPrice(float $extendedUnitPrice): self
    {
        $this->extendedUnitPrice = $extendedUnitPrice;

        return $this;
    }

    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = round($totalPrice, 2);

        return $this;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setQuantity(float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }

    public function setDiscount(float $discount): self
    {
        $this->discount = $discount;

        return $this;
    }

    public function setSalesUnit(string $salesUnit): self
    {
        $this->salesUnit = $salesUnit;

        return $this;
    }

    public function getSalesUnit(): string
    {
        return $this->salesUnit;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function setCommission(float $commission): self
    {
        $this->commission = $commission;

        return $this;
    }

    public function getCommission(): float
    {
        return $this->commission;
    }

    public function setLeadtime(int $leadtime): self
    {
        $this->leadtime = $leadtime;

        return $this;
    }

    public function getLeadtime(): int
    {
        return $this->leadtime;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setQuotedAt(?\DateTimeInterface $quotedAt): self
    {
        $this->quotedAt = $quotedAt;

        return $this;
    }

    public function getQuotedAt(): ?\DateTimeInterface
    {
        return $this->quotedAt;
    }

    public function setClosedAt(?\DateTime $closedAt): self
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getTax(): float
    {
        return $this->tax;
    }

    public function setTax(float $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getCountryOfOrigin(): ?string
    {
        return $this->countryOfOrigin;
    }

    public function setCountryOfOrigin(?string $countryOfOrigin): self
    {
        $this->countryOfOrigin = $countryOfOrigin;

        return $this;
    }

    public function getCommodityCode(): ?string
    {
        return $this->commodityCode;
    }

    public function setCommodityCode(?string $commodityCode): self
    {
        $this->commodityCode = $commodityCode;

        return $this;
    }

    public function getItemType(): string
    {
        return $this->itemType;
    }

    public function setItemType(string $itemType): self
    {
        $this->itemType = $itemType;

        return $this;
    }

    public function isQuoted(): bool
    {
        return self::QUOTED === $this->status;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
