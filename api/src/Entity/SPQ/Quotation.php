<?php

declare(strict_types=1);

namespace App\Entity\SPQ;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\File\DownloadController;
use App\Doctrine\Mapping\Attributes as App;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Entity\BaanAddress;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Serializer\Filter\ContextFilter;
use App\Serializer\Filter\PropertyFilter;
use App\Validator\Constraints\EmailList;
use App\Validator\Constraints\EmailListValidator;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity]
#[UniqueEntity(fields: ['sph', 'baanCustomerNumber', 'customerPurchaseOrder'], message: 'This purchase order number has already been used for this customer in this company.', errorPath: 'customerPurchaseOrder', ignoreNull: true)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['quotation', 'people_public', 'expose_legacy', 'file']]),
        new Get(),
        new Get(
            uriTemplate: '/quotations/{id}/attached_files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'attachedFiles', fromClass: AttachedFile::class),
                'id' => new Link(fromClass: Quotation::class),
            ],
            defaults: ['parentProperty' => 'quotation', 'class' => AttachedFile::class],
            controller: DownloadController::class,
            name: 'download_attached_file',
        ),
        new Get(
            uriTemplate: '/quotations/{id}/quotation_files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'quotationFiles', fromClass: QuotationFile::class),
                'id' => new Link(fromClass: Quotation::class),
            ],
            defaults: ['parentProperty' => 'quotation', 'class' => QuotationFile::class],
            controller: DownloadController::class,
            name: 'download_quotation_file',
        ),
    ],
    routePrefix: 'parts',
    normalizationContext: ['groups' => ['quotation:detail', 'quotation_lines', 'people_public', 'expose_legacy', 'file', 'baan_address']],
    denormalizationContext: ['groups' => []]
)]
#[ORM\Table(name: 'spq_quotations')]
#[ORM\Index(columns: ['routing'])]
#[ORM\Index(columns: ['status'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['status', 'poster', 'source', 'quoter', 'sph', 'rfq' => 'partial', 'quotationLines.partNumber' => 'partial', 'baanCustomerNumber', 'baanSalesOrder', 'customerName' => 'partial', 'contactEmails' => 'partial', 'customerPurchaseOrder' => 'partial'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt', 'receivedAt', 'expiredAt', 'submittedAt' => 'exclude_null', 'suspendedAt' => 'exclude_null', 'closedAt' => 'exclude_null'])]
#[ApiFilter(BooleanFilter::class, properties: ['payableService'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['quotation_totals', 'location_detail']])]
#[ApiFilter(PropertyFilter::class, arguments: ['whitelist' => ['id', 'createdAt', 'receivedAt', 'suspendedAt', 'expiredAt', 'closedAt', 'firstSubmittedAt', 'submittedAt', 'poster' => ['firstname', 'lastname', 'email'], 'source' => ['firstname', 'lastname', 'email'], 'quoter' => ['firstname', 'lastname', 'email'], 'sph' => ['name', 'erp'], 'rfq', 'baanCustomerNumber', 'baanCustomerName', 'baanSalesOrder', 'customerPurchaseOrder', 'status', 'requestType' => ['name'], 'customerName', 'contactEmails', 'currency', 'reason']])]
#[ApiFilter(ContextFilter::class)]
#[App\Loggable]
class Quotation
{
    /**
     * @var string
     */
    final public const ORDERED = 'ORDERED';
    /**
     * @var string
     */
    final public const PENDING = 'PENDING';
    /**
     * @var string
     */
    final public const SUSPENDED = 'SUSPENDED';
    /**
     * @var string
     */
    final public const SUBMITTED_PARTIAL = 'SUBMITTED_PARTIAL';
    /**
     * @var string
     */
    final public const SUBMITTED_FULL = 'SUBMITTED_FULL';
    /**
     * @var string
     */
    final public const SUBMITTED_FULL_REVISED = 'SUBMITTED_FULL_REVISED';
    /**
     * @var string
     */
    final public const ORDERED_PARTIAL = 'ORDERED_PARTIAL';
    /**
     * @var string
     */
    final public const ORDERED_FULL = 'ORDERED_FULL';
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
    final public const LOST_FOR_PRICE = 'PRICE';
    /**
     * @var string
     */
    final public const LOST_FOR_LEADTIME = 'LEADTIME';
    /**
     * @var string
     */
    final public const LOST_FOR_RELATIONSHIP = 'RELATIONSHIP';
    /**
     * @var string
     */
    final public const LOST_FOR_OTHER = 'OTHER';
    /**
     * @var string
     */
    final public const LOST_AUTOMATIC = 'AUTOMATIC CLOSURE';

    /**
     * @var string
     */
    final public const INTRANET_SHOW_PATH = '/parts/spq/quotations/%d/show';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['quotation', 'quotation:detail', 'quotation_lines:reports'])]
    private ?int $id = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['quotation', 'quotation:detail'])]
    #[Exclude]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(name: 'received_at', type: 'date')]
    #[Groups(['quotation', 'quotation:detail'])]
    #[Exclude]
    private \DateTimeInterface $receivedAt;

    #[ORM\Column(name: 'suspended_at', type: 'datetime', nullable: true)]
    #[Groups(['quotation', 'quotation:detail'])]
    #[Exclude]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: self::SUSPENDED)]
    private ?\DateTimeInterface $suspendedAt = null;

    #[ORM\Column(name: 'expired_at', type: 'datetime')]
    #[Groups(['quotation', 'quotation:detail'])]
    #[Exclude]
    private \DateTimeInterface $expiredAt;

    #[ORM\Column(name: 'closed_at', type: 'datetime', nullable: true)]
    #[Groups(['quotation', 'quotation:detail'])]
    #[Exclude]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: [self::LOST, self::CANCELLED, self::ORDERED_PARTIAL, self::ORDERED_FULL])]
    private ?\DateTimeInterface $closedAt = null;

    #[ORM\Column(name: 'submitted_at', type: 'datetime', nullable: true)]
    #[Groups(['quotation', 'quotation:detail'])]
    #[Exclude]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: [self::SUBMITTED_PARTIAL, self::SUBMITTED_FULL])]
    private ?\DateTimeInterface $submittedAt = null;

    #[ORM\Column(name: 'first_submitted_at', type: 'datetime', nullable: true)]
    #[Groups(['quotation', 'quotation:detail'])]
    #[Exclude]
    private ?\DateTimeInterface $firstSubmittedAt = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'poster_id')]
    #[Groups(['quotation', 'quotation:detail', 'quotation_lines:reports'])]
    #[ApiProperty(fetchEager: true)]
    #[Assert\NotNull]
    private ?People $poster = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'source_id', nullable: true)]
    #[Groups(['quotation', 'quotation:detail'])]
    #[ApiProperty(fetchEager: true)]
    private ?People $source = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'quoter_id', nullable: true)]
    #[Groups(['quotation', 'quotation:detail', 'quotation_lines:reports'])]
    #[ApiProperty(fetchEager: true)]
    private ?People $quoter = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(name: 'sph_id')]
    #[Groups(['quotation', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull]
    #[ApiProperty(fetchEager: true)]
    #[ValidLocation(sparePartsHub: true)]
    private ?Location $sph = null;

    #[ORM\Column(name: 'rfq', type: 'string', length: 255, nullable: true)]
    #[Groups(['quotation', 'quotation:detail'])]
    private ?string $rfq = null;

    #[ORM\Column(name: 'baan_customer_number', type: 'string', length: 6)]
    #[Groups(['quotation', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\Length(min: 3, max: 6)]
    #[Assert\Regex('/^(\S){3,6}$/')]
    #[Assert\NotNull]
    private ?string $baanCustomerNumber = null;

    #[ORM\Column(name: 'baan_customer_name', type: 'string', length: 50)]
    #[Groups(['quotation', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private string $baanCustomerName;

    #[ORM\Column(name: 'baan_sales_order', type: 'integer', nullable: true)]
    #[Groups(['quotation', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\Range(min: 1, max: 999999)]
    private ?int $baanSalesOrder = null;

    #[ORM\Column(name: 'customer_purchase_order', type: 'string', nullable: true)]
    #[Groups(['quotation', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\NotNull(groups: [self::ORDERED])]
    #[Assert\Regex(pattern: '/^[a-z0-9_ .\-\/:]+$/i', message: "Authorized characters are 'a' to 'z' without accent (lower and upper case), '-', '_', '/', '.', ':' and ' '.")]
    #[Assert\Length(max: 30)]
    private ?string $customerPurchaseOrder = null;

    #[ORM\Column(name: 'status', type: 'string', length: 25)]
    #[Groups(['quotation', 'quotation:detail', 'quotation_lines:reports'])]
    private string $status = self::PENDING;

    #[ORM\Column(name: 'payable_service', type: 'boolean')]
    #[Groups(['quotation', 'quotation:detail', 'quotation_lines:reports'])]
    #[Assert\Type('boolean')]
    private bool $payableService = false;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\SPQ\RequestType', inversedBy: 'quotations')]
    #[Groups(['quotation', 'quotation:detail'])]
    #[ApiProperty(fetchEager: true)]
    #[Assert\NotNull]
    private ?RequestType $requestType = null;

    #[ORM\Column(name: 'customer_name', type: 'string', length: 255, nullable: true)]
    #[Groups(['quotation', 'quotation:detail'])]
    #[Assert\NotNull]
    private ?string $customerName = null;

    #[ORM\Column(name: 'contact_emails', type: 'text', length: 65535)]
    #[Groups(['quotation', 'quotation:detail'])]
    #[Assert\NotBlank]
    #[EmailList(mode: 'strict')]
    private string $contactEmails;

    #[ORM\Column(name: 'comment', type: 'text', length: 65535)]
    #[Groups(['quotation:detail'])]
    private string $comment = '';

    #[ORM\Column(name: 'shipped_complete', type: 'boolean')]
    #[Groups(['quotation:detail'])]
    #[Assert\Type('boolean')]
    private bool $shippedComplete = false;

    #[ORM\Column(name: 'payment_terms', type: 'string', length: 255)]
    #[Groups(['quotation:detail'])]
    #[Assert\Length(min: 2, max: 3)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private ?string $paymentTerms = null;

    #[ORM\Column(name: 'delivery_address_baan_id', type: 'string', length: 3, nullable: false)]
    #[Groups(['quotation:detail'])]
    #[Assert\NotIdenticalTo(value: 'oth', groups: [self::ORDERED])]
    #[Assert\NotNull]
    private string $deliveryAddressBaanId;

    #[ORM\Embedded(class: 'App\Entity\BaanAddress', columnPrefix: 'delivery_')]
    #[Groups(['quotation:detail', 'baan_address_write'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private BaanAddress $deliveryAddress;

    #[ORM\Column(name: 'billing_address_baan_id', type: 'string', length: 3, nullable: false)]
    #[Groups(['quotation:detail'])]
    #[Assert\NotIdenticalTo(value: 'oth', groups: [self::ORDERED])]
    #[Assert\NotNull]
    private string $billingAddressBaanId;

    #[ORM\Embedded(class: 'App\Entity\BaanAddress', columnPrefix: 'billing_')]
    #[Groups(['quotation:detail', 'baan_address_write'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private BaanAddress $billingAddress;

    #[ORM\Column(name: 'language', type: 'string', length: 255)]
    #[Groups(['quotation:detail'])]
    #[Assert\NotNull]
    #[Assert\Length(min: 2, max: 2, exactMessage: 'Language must be 2 letters long')]
    private ?string $language = null;

    #[ORM\Column(name: 'header_text', type: 'text', length: 65535)]
    #[Groups(['quotation:detail'])]
    private ?string $headerText = '';

    #[ORM\Column(name: 'incoterms', type: 'string', length: 3)]
    #[Groups(['quotation:detail'])]
    #[Assert\NotNull]
    #[Assert\Length(min: 3, max: 3)]
    private ?string $incoterms = null;

    #[ORM\Column(name: 'incoterms_location', type: 'string', length: 20, nullable: true)]
    #[Groups(['quotation:detail'])]
    #[Assert\Length(max: 20)]
    private ?string $incotermsLocation = null;

    /**
     * @var Collection<QuotationLine>
     */
    #[ORM\OneToMany(mappedBy: 'quotation', targetEntity: 'App\Entity\SPQ\QuotationLine', cascade: ['persist'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Groups(['quotation:detail'])]
    #[ApiProperty(fetchEager: true)]
    #[Exclude]
    private Collection $quotationLines;

    /**
     * @var Collection<AttachedFile>
     */
    #[ORM\OneToMany(mappedBy: 'quotation', targetEntity: 'App\Entity\SPQ\AttachedFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['quotation:detail'])]
    private Collection $attachedFiles;

    /**
     * @var Collection<QuotationFile>
     */
    #[ORM\OneToMany(mappedBy: 'quotation', targetEntity: 'App\Entity\SPQ\QuotationFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['quotation:detail'])]
    #[Exclude]
    private Collection $quotationFiles;

    #[ORM\Column(name: 'forwarding_agent', type: 'string', length: 3, nullable: true)]
    #[Groups(['quotation:detail'])]
    private ?string $forwardingAgent = null;

    #[ORM\Column(name: 'currency', type: 'string', length: 3)]
    #[Groups(['quotation', 'quotation:detail'])]
    #[Assert\NotNull]
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['EUR', 'USD', 'RMB', 'HKD', 'AED'])]
    private string $currency;

    #[ORM\Column(name: 'default_commission', type: 'float')]
    #[Groups(['quotation:detail'])]
    #[Assert\Type('float')]
    #[Assert\Range(min: 0, max: 99)]
    private float $defaultCommission = 0.0;

    #[ORM\Column(type: 'string', length: 25, nullable: true)]
    #[Groups(['quotation:detail', 'quotation_totals', 'quotation_lines:reports'])]
    private ?string $reason = null;

    #[ORM\Column(name: 'routing', type: 'string', length: 4, nullable: true)]
    private ?string $routing = null;

    public function __construct()
    {
        $this->quotationLines = new ArrayCollection();
        $this->attachedFiles = new ArrayCollection();
        $this->quotationFiles = new ArrayCollection();
        $this->deliveryAddress = new BaanAddress();
        $this->billingAddress = new BaanAddress();
    }

    public function reset(): self
    {
        $this->id = null;
        $this->status = self::PENDING;
        $this->attachedFiles = new ArrayCollection();
        $this->quotationFiles = new ArrayCollection();

        $this->createdAt = null;
        $this->closedAt = null;
        $this->suspendedAt = null;
        $this->submittedAt = null;
        $this->firstSubmittedAt = null;

        $this->poster = null;
        $this->quoter = null;

        $this->baanSalesOrder = null;
        $this->customerPurchaseOrder = null;
        $this->reason = null;
        $this->routing = null;
        $this->setQuotationLines(
            $this->quotationLines
                ->filter(static fn (QuotationLine $line) => QuotationLine::DELETED !== $line->getStatus())
                ->map(fn (QuotationLine $line) => (clone $line)->reset()->setQuotation($this)));

        return $this;
    }

    /**
     * @return Collection<QuotationLine>
     */
    public function getQuotationLines(): Collection
    {
        return $this->quotationLines;
    }

    public function addQuotationLine(QuotationLine $quotationLine): self
    {
        $this->quotationLines->add($quotationLine);
        $quotationLine->setQuotation($this);

        return $this;
    }

    public function removeQuotationLine(QuotationLine $quotationLine): self
    {
        $this->quotationLines->removeElement($quotationLine);
        $quotationLine->setQuotation(null);

        return $this;
    }

    public function setQuotationLines(Collection $quotationLines): self
    {
        $this->quotationLines = $quotationLines;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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

    public function setReceivedAt(\DateTime $receivedAt): self
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    public function getReceivedAt(): \DateTimeInterface
    {
        return $this->receivedAt;
    }

    public function setSuspendedAt(?\DateTimeInterface $suspendedAt): self
    {
        $this->suspendedAt = $suspendedAt;

        return $this;
    }

    public function getSuspendedAt(): ?\DateTimeInterface
    {
        return $this->suspendedAt;
    }

    public function setExpiredAt(\DateTime $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    public function getExpiredAt(): \DateTimeInterface
    {
        return $this->expiredAt;
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

    public function setSubmittedAt(?\DateTime $submittedAt): self
    {
        $this->submittedAt = $submittedAt;

        return $this;
    }

    public function getSubmittedAt(): ?\DateTimeInterface
    {
        return $this->submittedAt;
    }

    public function getFirstSubmittedAt(): ?\DateTimeInterface
    {
        return $this->firstSubmittedAt;
    }

    public function setFirstSubmittedAt(?\DateTimeInterface $firstSubmittedAt): self
    {
        $this->firstSubmittedAt = $firstSubmittedAt;

        return $this;
    }

    public function setPoster(People $poster): self
    {
        $this->poster = $poster;

        return $this;
    }

    public function getPoster(): People
    {
        return $this->poster;
    }

    public function setSource(?People $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function getSource(): ?People
    {
        return $this->source;
    }

    public function setQuoter(?People $quoter): self
    {
        $this->quoter = $quoter;

        return $this;
    }

    public function getQuoter(): ?People
    {
        return $this->quoter;
    }

    public function setSph(Location $sph): self
    {
        $this->sph = $sph;

        return $this;
    }

    public function getSph(): Location
    {
        return $this->sph;
    }

    public function setRfq($rfq): self
    {
        $this->rfq = $rfq;

        return $this;
    }

    public function getRfq(): ?string
    {
        return $this->rfq;
    }

    public function setBaanCustomerNumber(?string $baanCustomerNumber): self
    {
        $this->baanCustomerNumber = $baanCustomerNumber;

        return $this;
    }

    public function getBaanCustomerNumber(): string
    {
        return $this->baanCustomerNumber;
    }

    public function setBaanCustomerName(string $baanCustomerName): self
    {
        $this->baanCustomerName = $baanCustomerName;

        return $this;
    }

    public function getBaanCustomerName(): string
    {
        return $this->baanCustomerName;
    }

    public function setBaanSalesOrder(?int $baanSalesOrder): self
    {
        $this->baanSalesOrder = $baanSalesOrder;

        return $this;
    }

    public function getBaanSalesOrder(): ?int
    {
        return $this->baanSalesOrder;
    }

    public function getCustomerPurchaseOrder(): ?string
    {
        return $this->customerPurchaseOrder;
    }

    public function setCustomerPurchaseOrder(?string $customerPurchaseOrder): self
    {
        $this->customerPurchaseOrder = $customerPurchaseOrder;

        return $this;
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

    public function setRequestType(RequestType $requestType): self
    {
        $this->requestType = $requestType;

        return $this;
    }

    public function getRequestType(): RequestType
    {
        return $this->requestType;
    }

    public function setCustomerName(?string $customerName): self
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setContactEmails(string $contactEmails): self
    {
        $this->contactEmails = mb_trim(mb_trim($contactEmails), EmailListValidator::LIST_SEPARATOR);

        return $this;
    }

    public function getContactEmails(): string
    {
        return $this->contactEmails;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setShippedComplete(bool $shippedComplete): self
    {
        $this->shippedComplete = $shippedComplete;

        return $this;
    }

    public function isShippedComplete(): bool
    {
        return $this->shippedComplete;
    }

    public function setPaymentTerms(?string $paymentTerms): self
    {
        $this->paymentTerms = $paymentTerms;

        return $this;
    }

    public function getPaymentTerms(): ?string
    {
        return $this->paymentTerms;
    }

    public function getDeliveryAddressBaanId(): ?string
    {
        return $this->deliveryAddressBaanId;
    }

    public function setDeliveryAddress(BaanAddress $deliveryAddress): self
    {
        $this->deliveryAddress = $deliveryAddress;

        return $this;
    }

    public function setDeliveryAddressBaanId(string $deliveryAddressBaanId): self
    {
        $this->deliveryAddressBaanId = $deliveryAddressBaanId;

        return $this;
    }

    public function getDeliveryAddress(): BaanAddress
    {
        return $this->deliveryAddress;
    }

    public function getBillingAddressBaanId(): ?string
    {
        return $this->billingAddressBaanId;
    }

    public function setBillingAddressBaanId(string $billingAddressBaanId): self
    {
        $this->billingAddressBaanId = $billingAddressBaanId;

        return $this;
    }

    public function setBillingAddress(BaanAddress $billingAddress): self
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    public function getBillingAddress(): BaanAddress
    {
        return $this->billingAddress;
    }

    public function setLanguage(?string $language): self
    {
        $this->language = $language;

        return $this;
    }

    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setHeaderText(?string $headerText): self
    {
        $this->headerText = $headerText;

        return $this;
    }

    public function getHeaderText(): ?string
    {
        return $this->headerText;
    }

    public function getIncoterms(): ?string
    {
        return $this->incoterms;
    }

    public function setIncoterms(?string $incoterms): self
    {
        $this->incoterms = $incoterms;

        return $this;
    }

    public function getIncotermsLocation(): ?string
    {
        return $this->incotermsLocation;
    }

    public function setIncotermsLocation(?string $incotermsLocation): self
    {
        $this->incotermsLocation = $incotermsLocation;

        return $this;
    }

    /**
     * @return Collection<AttachedFile>
     */
    public function getAttachedFiles(): Collection
    {
        return $this->attachedFiles;
    }

    public function addAttachedFile(AttachedFile $attachedFile): self
    {
        $this->attachedFiles[] = $attachedFile;
        $attachedFile->setQuotation($this);

        return $this;
    }

    public function removeAttachedFile(AttachedFile $attachedFile): self
    {
        $this->attachedFiles->removeElement($attachedFile);

        return $this;
    }

    /**
     * @return Collection<QuotationFile>
     */
    public function getQuotationFiles(): Collection
    {
        return $this->quotationFiles;
    }

    public function addQuotationFile(QuotationFile $quotationFile): self
    {
        $this->quotationFiles[] = $quotationFile;
        $quotationFile->setQuotation($this);

        return $this;
    }

    public function removeQuotationFile(QuotationFile $quotationFile): self
    {
        $this->quotationFiles->removeElement($quotationFile);

        return $this;
    }

    public function getForwardingAgent(): ?string
    {
        return $this->forwardingAgent;
    }

    public function setForwardingAgent(?string $forwardingAgent): self
    {
        $this->forwardingAgent = $forwardingAgent;

        return $this;
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

    public function setDefaultCommission(float $defaultCommission): self
    {
        $this->defaultCommission = $defaultCommission;

        return $this;
    }

    public function getDefaultCommission(): float
    {
        return $this->defaultCommission;
    }

    public function getRouting(): ?string
    {
        return $this->routing;
    }

    public function setRouting(?string $routing): self
    {
        $this->routing = $routing;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function isPayableService(): bool
    {
        return $this->payableService;
    }

    public function setPayableService(bool $payableService): self
    {
        $this->payableService = $payableService;

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        $tz = null;
        try {
            $tz = new \DateTimeZone($this->sph->getTimeZone());
        } catch (\Exception $exception) {
        }

        $today = new \DateTime('today', $tz);
        if (self::LOST !== $this->status && $this->expiredAt < $today) {
            $context
                ->buildViolation(\sprintf('This value should be greater than %s.', $today->format('Y-m-d')))
                ->atPath('expiredAt')
                ->addViolation()
            ;
        }
        $tomorrow = new \DateTime('tomorrow', $tz);
        if ($this->receivedAt > $tomorrow) {
            $context
                ->buildViolation(\sprintf('This value should be less than %s.', $tomorrow->format('Y-m-d')))
                ->atPath('receivedAt')
                ->addViolation()
            ;
        }
    }
}
