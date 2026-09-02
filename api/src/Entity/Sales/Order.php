<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Controller\Sales\OrderController;
use App\Doctrine\Mapping\Attributes\Exclude;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Doctrine\Mapping\Attributes\Transferable;
use App\Entity\Directory\JuridicalLocation;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\UpdatableStatusEntityInterface;
use App\Filter\ColumnsFilter;
use App\Serializer\Filter\ContextFilter;
use App\Validator\Constraints\Location as ValidLocation;
use App\Validator\Constraints\SalesOrder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Mapping\Attributes\Synchronize;
use LegacyBundle\Doctrine\Transformer\ArrayToString;
use LegacyBundle\Doctrine\Transformer\BooleanToChar;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\StrippedString;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\Sales\OrderRepository')]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['sales_order', 'people_public', 'location_public', 'expose_legacy', 'customer_public', 'customer:status', 'juridical_location:public']],
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
        ),
        new Put(security: "is_granted('FEATURE_SALES_ORDER_EDIT') or is_granted('SALES_ORDER_EDIT_VOTER', object)"),
        new Put(
            uriTemplate: '/orders/{id}/status',
            openapi: new Operation(
                parameters: [
                    new Parameter(
                        name: 'status',
                        in: 'path',
                        description: 'Status',
                        required: true,
                        schema: ['type' => 'string'],
                    ),
                ],
            ),
            denormalizationContext: ['groups' => ['sales_order:status']],
            security: "is_granted('FEATURE_SALES_ORDER_STATUS_UPDATE')",
            name: 'update_order_status',
        ),
        new Post(security: "is_granted('FEATURE_SALES_ORDER_CREATE')"),
        new Post(
            uriTemplate: '/orders/{id}/duplicate',
            controller: OrderController::class,
            security: "is_granted('FEATURE_SALES_ORDER_CREATE')",
            deserialize: false,
            name: 'duplicate_order',
        ),
        new Post(
            uriTemplate: '/orders/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => OrderFile::class],
            controller: UploadController::class,
            security: "is_granted('FEATURE_SALES_ORDER_CREATE')",
            deserialize: false,
            name: 'upload_order_file'
        ),
        new Get(),
        new Get(
            uriTemplate: '/orders/{id<\d+>}/files/{fileId<\d+>}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: OrderFile::class),
                'id' => new Link(fromClass: Order::class),
            ],
            defaults: ['parentProperty' => 'order', 'class' => OrderFile::class],
            controller: DownloadController::class,
            name: 'download_order_file',
        ),
        new Delete(security: "is_granted('FEATURE_SALES_ORDER_EDIT')"),
        new Delete(
            uriTemplate: '/orders/{id<\d+>}/files/{fileId<\d+>}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: OrderFile::class),
                'id' => new Link(fromClass: Order::class),
            ],
            defaults: ['parentProperty' => 'order', 'class' => OrderFile::class],
            controller: DeleteController::class,
            security: "is_granted('FEATURE_SALES_ORDER_EDIT')",
            name: 'delete_order_file',
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['sales_order:detail', 'people_public', 'location_public', 'expose_legacy', 'customer_public', 'customer:status', 'customer_type', 'juridical_location:public', 'file']],
    denormalizationContext: ['groups' => ['sales_order:write']],
    security: "is_granted('FEATURE_SALES_ORDER_READ')",
)]
#[ORM\Table(name: 'sales_orders')]
#[ApiFilter(SearchFilter::class, properties: ['status', 'endUser', 'buyer', 'sso', 'asm', 'baanOrderNumbers' => 'partial', 'customerPurchaseOrders' => 'partial',  'inforLnBusinessPartnerCode', 'equoteId', 'juridicalLocation', 'salesAgent', 'legacyId', 'baanCustomerNumber'])]
#[ApiFilter(OrderFilter::class, properties: ['enteredAt', 'id', 'equoteId', 'status', 'sso.name', 'juridicalLocation.name', 'asm.lastname'])]
#[ApiFilter(DateFilter::class, properties: ['enteredAt'])]
#[ApiFilter(BooleanFilter::class, properties: ['newCustomer'])]
#[ApiFilter(ContextFilter::class)]
#[ApiFilter(ColumnsFilter::class)]
#[Loggable]
#[SalesOrder]
#[Synchronize(table: 'sor')]
class Order implements UpdatableStatusEntityInterface
{
    use LegacyIdentifierTrait;

    /**
     * @var string
     */
    final public const PENDING = 'PENDING';
    /**
     * @var string
     */
    final public const IN_PROGRESS = 'IN PROGRESS';
    /**
     * @var string
     */
    final public const CLOSED = 'CLOSED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:light'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:write'])]
    #[ValidLocation(sso: true)]
    #[Legacy\Column(column: 'bu', transformer: ObjectToProperty::class, options: ['property' => 'erp'])]
    #[Legacy\Column(column: 'sso', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?Location $sso = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\JuridicalLocation')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotNull]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:write'])]
    #[Legacy\Column(column: 'juridical_entity_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?JuridicalLocation $juridicalLocation = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    #[Assert\Length(max: 10)]
    private ?string $baanCustomerNumber = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:write'])]
    #[Assert\Length(max: 10)]
    #[Legacy\Column(column: 't_cuno')]
    private ?string $inforLnBusinessPartnerCode = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:light'])]
    #[Exclude]
    #[Legacy\Column(column: 'dt_entered', transformer: DateTimeToString::class)]
    #[Gedmo\Timestampable(on: 'create')]
    private ?\DateTimeInterface $enteredAt = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['sales_order:detail'])]
    #[Exclude]
    #[Legacy\Column(column: 'dt_closed', transformer: DateTimeToString::class)]
    #[Gedmo\Timestampable(on: 'change', field: 'status', value: self::CLOSED)]
    private ?\DateTimeInterface $closedAt = null;

    #[ORM\Column(type: 'string', length: 20)]
    #[Assert\NotNull]
    #[Assert\Choice(callback: 'getStatuses')]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:light', 'sales_order:status'])]
    #[Legacy\Column(column: 'status', transformer: StrippedString::class, options: ['search' => ' ', 'replace' => '_'])]
    private string $status = self::PENDING;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:write'])]
    #[Assert\Length(max: 20)]
    #[Legacy\Column(column: 'eqno')]
    private ?string $equoteId = null;

    // In legacy the column length is 255 > (27 * 9)
    #[ORM\Column(type: 'simple_array', length: 255, nullable: true)]
    #[Assert\Count(max: 27)]
    #[Assert\All([new Assert\Type(type: 'string'), new Assert\Length(max: 9)])]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:write'])]
    #[Legacy\Column(column: 'orno', transformer: ArrayToString::class)]
    private array $baanOrderNumbers = [];

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotNull]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:write'])]
    #[Transferable(handler: 'handler.sales_orders.asm')]
    #[Legacy\Column(column: 'asm', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?People $asm = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotNull]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:write'])]
    #[Legacy\Column(column: 'buyer_customer_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?Customer $buyer = null;

    #[ORM\ManyToOne(targetEntity: ExtranetUser::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:write'])]
    private ?ExtranetUser $contact;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[ORM\JoinColumn(nullable: true)]
    #[Assert\NotNull]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:write'])]
    #[Legacy\Column(column: 'user_customer_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private ?Customer $endUser = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotNull]
    #[Groups(['sales_order:detail'])]
    #[Legacy\Column(column: 'cu_nama')]
    private string $customerName;

    #[ORM\Column(type: 'boolean')]
    #[Assert\Type('boolean')]
    #[Groups(['sales_order:detail', 'sales_order:write'])]
    #[Legacy\Column(column: 'cu_new', transformer: BooleanToChar::class)]
    private bool $newCustomer = false;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:write'])]
    #[Legacy\Column(column: 'agnt_nama', transformer: ObjectToProperty::class, options: ['property' => 'name'])]
    private ?Customer $salesAgent = null;

    #[ORM\Column(type: 'simple_array', length: 210, nullable: true)]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:write'])]
    #[Assert\Count(min: 1, max: 8)]
    #[Assert\All([new Assert\Type(type: 'string'), new Assert\Length(max: 30)])]
    #[Legacy\Column(column: 'cu_orno', transformer: ArrayToString::class)]
    private array $customerPurchaseOrders = [];

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['sales_order:detail', 'sales_order:write'])]
    #[Exclude]
    #[Legacy\Column(column: 'src_xml')]
    private ?string $xmlSource = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['sales_order', 'sales_order:detail', 'sales_order:write'])]
    #[Legacy\Column(column: 'note')]
    private ?string $note = null;

    /**
     * @var Collection<OrderFile>
     */
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: 'App\Entity\Sales\OrderFile', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['sales_order:detail'])]
    private Collection $files;

    /**
     * @var Collection<OrderLine>
     */
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderLine::class, cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['sales_order:detail'])]
    private Collection $lines;

    public function __construct()
    {
        $this->files = new ArrayCollection();
        $this->lines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSso(): ?Location
    {
        return $this->sso;
    }

    public function setSso(?Location $sso): self
    {
        $this->sso = $sso;

        return $this;
    }

    public function getJuridicalLocation(): ?JuridicalLocation
    {
        return $this->juridicalLocation;
    }

    public function setJuridicalLocation(?JuridicalLocation $juridicalLocation): self
    {
        $this->juridicalLocation = $juridicalLocation;

        return $this;
    }

    public function getBaanCustomerNumber(): ?string
    {
        return $this->baanCustomerNumber;
    }

    public function setBaanCustomerNumber(?string $baanCustomerNumber): self
    {
        $this->baanCustomerNumber = $baanCustomerNumber;

        return $this;
    }

    public function getInforLnBusinessPartnerCode(): ?string
    {
        return $this->inforLnBusinessPartnerCode;
    }

    public function setInforLnBusinessPartnerCode(?string $inforLnBusinessPartnerCode): self
    {
        $this->inforLnBusinessPartnerCode = $inforLnBusinessPartnerCode;

        return $this;
    }

    public function getEnteredAt(): ?\DateTimeInterface
    {
        return $this->enteredAt;
    }

    public function setEnteredAt(\DateTimeInterface $enteredAt): self
    {
        $this->enteredAt = $enteredAt;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeInterface $closedAt): self
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getEquoteId(): ?string
    {
        return $this->equoteId;
    }

    public function setEquoteId(?string $equoteId): self
    {
        $this->equoteId = $equoteId;

        return $this;
    }

    public function getBaanOrderNumbers(): array
    {
        return $this->baanOrderNumbers;
    }

    public function setBaanOrderNumbers(array $baanOrderNumbers): self
    {
        $this->baanOrderNumbers = $baanOrderNumbers;

        return $this;
    }

    public function getAsm(): ?People
    {
        return $this->asm;
    }

    public function setAsm(?People $asm): self
    {
        $this->asm = $asm;

        return $this;
    }

    public function getBuyer(): ?Customer
    {
        return $this->buyer;
    }

    public function setBuyer(?Customer $buyer): self
    {
        $this->buyer = $buyer;

        return $this;
    }

    public function getContact(): ?ExtranetUser
    {
        return $this->contact;
    }

    public function setContact(?ExtranetUser $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getEndUser(): ?Customer
    {
        return $this->endUser;
    }

    public function setEndUser(?Customer $endUser): self
    {
        $this->endUser = $endUser;

        return $this;
    }

    public function getCustomerName(): ?string
    {
        return $this->customerName;
    }

    public function setCustomerName(string $customerName): self
    {
        $this->customerName = $customerName;

        return $this;
    }

    public function getNewCustomer(): bool
    {
        return $this->newCustomer;
    }

    public function setNewCustomer(bool $newCustomer): self
    {
        $this->newCustomer = $newCustomer;

        return $this;
    }

    public function getSalesAgent(): ?Customer
    {
        return $this->salesAgent;
    }

    public function setSalesAgent(?Customer $salesAgent): self
    {
        $this->salesAgent = $salesAgent;

        return $this;
    }

    public function getCustomerPurchaseOrders(): array
    {
        return $this->customerPurchaseOrders;
    }

    public function setCustomerPurchaseOrders(array $customerPurchaseOrders): self
    {
        $this->customerPurchaseOrders = $customerPurchaseOrders;

        return $this;
    }

    public function getXmlSource(): ?string
    {
        return $this->xmlSource;
    }

    public function setXmlSource(?string $xmlSource): self
    {
        $this->xmlSource = $xmlSource;

        return $this;
    }

    /**
     * @return Collection<OrderFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(OrderFile $file): self
    {
        $this->files[] = $file;
        $file->setOrder($this);

        return $this;
    }

    public function removeFile(OrderFile $file): self
    {
        $this->files->removeElement($file);

        return $this;
    }

    /**
     * @return Collection<OrderLine>
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    public function addLine(OrderLine $line): self
    {
        $line->order = $this;
        $this->lines->add($line);

        return $this;
    }

    public function removeLine(OrderLine $line): self
    {
        $this->lines->removeElement($line);

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getStatuses()
    {
        return [
            self::PENDING,
            self::IN_PROGRESS,
            self::CLOSED,
        ];
    }

    public function reset(): self
    {
        $this->id = null;
        $this->status = self::PENDING;
        $this->enteredAt = null;
        $this->closedAt = null;
        $this->equoteId = null;
        $this->baanOrderNumbers = [];
        $this->xmlSource = null;

        return $this;
    }
}
