<?php

declare(strict_types=1);

namespace App\Entity\Sales\EquipmentShippingRecord;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
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
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Controller\File\DeleteController;
use App\Controller\File\DownloadController;
use App\Controller\File\UploadController;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\Location;
use App\Entity\FreightForwarder;
use App\Entity\Sales\Customer;
use App\Entity\Sales\Incoterm;
use App\Entity\UpdatableStatusEntityInterface;
use App\Validator\Constraints\EquipmentShippingRecordLineLoad;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['equipment_shipping_records', 'location_public', 'customer_light', 'country_list', 'incoterm', 'expose_legacy']]),
        new Put(
            security: "is_granted('FEATURE_ESR_WRITE')",
            securityPostDenormalize: "is_granted('EDIT_PICKUP_CONFIRMATION', object)"
        ),
        new Put(
            uriTemplate: '/equipment_shipping_records/{id}/status',
            denormalizationContext: ['groups' => ['equipment_shipping_records:write_status']],
            security: "is_granted('FEATURE_ESR_WRITE')",
            name: 'update_equipment_shipping_record_status',
        ),
        new Delete(security: "is_granted('FEATURE_ESR_WRITE')"),
        new Delete(
            uriTemplate: '/equipment_shipping_records/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: EquipmentShippingRecordFile::class),
                'id' => new Link(fromClass: EquipmentShippingRecord::class),
            ],
            defaults: ['parentProperty' => 'equipmentShippingRecord', 'class' => EquipmentShippingRecordFile::class],
            controller: DeleteController::class,
            normalizationContext: [],
            name: 'delete_equipment_shipping_record_file',
        ),
        new Post(security: "is_granted('FEATURE_ESR_WRITE')"),
        new Post(
            uriTemplate: '/equipment_shipping_records/{id}/files',
            inputFormats: ['multipart' => ['multipart/form-data']],
            outputFormats: ['jsonld'],
            defaults: ['method' => 'getFiles', 'class' => EquipmentShippingRecordFile::class],
            controller: UploadController::class,
            deserialize: false,
            validate: false,
            name: 'upload_equipment_shipping_record_file',
        ),
        new Get(),
        new Get(
            uriTemplate: '/equipment_shipping_records/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: EquipmentShippingRecordFile::class),
                'id' => new Link(fromClass: EquipmentShippingRecord::class),
            ],
            defaults: ['parentProperty' => 'equipmentShippingRecord', 'class' => EquipmentShippingRecordFile::class],
            controller: DownloadController::class,
            normalizationContext: [],
            name: 'download_equipment_shipping_record_file',
        ),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['equipment_shipping_records', 'equipment_shipping_records:detail', 'location_public', 'customer_light', 'country_list', 'currency', 'people_public', 'equipment_record', 'freight_forwarder', 'address', 'incoterm', 'expose_legacy', 'equipment_shipping_record_line:detail', 'equipment_shipping_record_cost:detail', 'file']],
    denormalizationContext: ['groups' => ['equipment_shipping_records:write']],
    forceEager: false,
)]
#[ApiFilter(OrderFilter::class, properties: ['id' => 'DESC'])]
#[ApiFilter(BooleanFilter::class, properties: ['shipAuthorization'])]
#[ApiFilter(SearchFilter::class, properties: [
    'legacyId' => 'exact',
    'customer',
    'status',
    'incoterm',
    'incoterm.code' => 'exact',
    'loadingPlace' => 'partial',
    'departurePlace' => 'partial',
    'arrivalPlace' => 'partial',
    'forwarder',
    'carrier',
    'modality',
    'equipmentShippingRecordLines.equipmentRecord.serialNumber',
    'equipmentShippingRecordLines.equipmentRecord.manufacturerLocation',
    'sso',
])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['equipment_shipping_records',  'equipment_shipping_record_line:details', 'equipment_shipping_records:detail', 'customer_light']])]

#[App\Loggable]
#[Legacy\Synchronize(table: 'esr')]
#[EquipmentShippingRecordLineLoad(errorPath: 'equipmentShippingRecordLines')]
class EquipmentShippingRecord implements LegacyIdInterface, UpdatableStatusEntityInterface
{
    use LegacyIdentifierTrait;

    final public const PENDING = 'PENDING';
    final public const BOOKED = 'BOOKED';
    final public const SHIPPED = 'SHIPPED';
    final public const CLOSED = 'CLOSED';

    final public const AIR = 'AIR';
    final public const ROAD = 'ROAD';
    final public const SEA = 'SEA';

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[Assert\NotNull]
    #[ValidLocation(sso: true)]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_records:write', 'equipment_shipping_record_line'])]
    #[Legacy\Column(column: 'sso_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public Location $sso;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Customer')]
    #[Assert\NotNull]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_records:detail', 'equipment_shipping_records:write'])]
    #[Legacy\Column(column: 'cuid', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    public ?Customer $customer = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Incoterm')]
    #[Assert\NotNull]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_records:detail', 'equipment_shipping_records:write'])]
    #[Legacy\Column(column: 'inco', transformer: ObjectToProperty::class, options: ['property' => 'code'])]
    public Incoterm $incoterm;

    #[ORM\Column(type: 'datetime')]
    #[Gedmo\Timestampable(on: 'create')]
    #[Groups(['equipment_shipping_records:detail'])]
    public \DateTime $createdAt;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Groups(['equipment_shipping_records:detail', 'equipment_shipping_records:write'])]
    #[Legacy\Column(column: 'load_place')]
    public ?string $loadingPlace = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Groups(['equipment_shipping_records:detail', 'equipment_shipping_records:write'])]
    #[Legacy\Column(column: 'departure')]
    public ?string $departurePlace = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    #[Groups(['equipment_shipping_records:detail', 'equipment_shipping_records:write'])]
    #[Legacy\Column(column: 'arrival')]
    public ?string $arrivalPlace = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\FreightForwarder')]
    #[Groups(['equipment_shipping_records:detail', 'equipment_shipping_records:write'])]
    public ?FreightForwarder $forwarder = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\FreightForwarder')]
    #[Groups(['equipment_shipping_records:detail', 'equipment_shipping_records:write'])]
    public ?FreightForwarder $carrier = null;

    #[ORM\Column(type: 'string', length: 25)]
    #[Assert\Choice(choices: [self::AIR, self::ROAD, self::SEA])]
    #[Groups(['equipment_shipping_records:detail', 'equipment_shipping_records:write', 'equipment_shipping_record_line'])]
    #[Legacy\Column(column: 'modality')]
    public string $modality = self::AIR;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['equipment_shipping_records:detail', 'equipment_shipping_records:write'])]
    #[Legacy\Column(column: 'notes')]
    public ?string $notes = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_records:detail', 'equipment_shipping_records:write'])]
    #[Legacy\Column(column: 'ship_auth')]
    public bool $shipAuthorization = false;

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: [self::PENDING, self::BOOKED, self::SHIPPED, self::CLOSED])]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_records:detail', 'equipment_shipping_records:write_status'])]
    #[Legacy\Column(column: 'status')]
    private string $status = self::PENDING;

    /**
     * @var Collection<EquipmentShippingRecordLine>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordLine', mappedBy: 'equipmentShippingRecord', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['equipment_shipping_records', 'equipment_shipping_records:write'])]
    #[Assert\Valid]
    private Collection $equipmentShippingRecordLines;

    /**
     * @var Collection<EquipmentShippingRecordCost>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordCost', mappedBy: 'equipmentShippingRecord', orphanRemoval: true)]
    #[Groups(['equipment_shipping_record_cost:detail'])]
    private Collection $equipmentShippingRecordCosts;

    /**
     * @var Collection<EquipmentShippingRecordFile>
     */
    #[ORM\OneToMany(targetEntity: 'App\Entity\Sales\EquipmentShippingRecord\EquipmentShippingRecordFile', mappedBy: 'equipmentShippingRecord', cascade: ['persist'], orphanRemoval: true)]
    #[Groups(['equipment_shipping_records:detail'])]
    private Collection $files;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['equipment_shipping_records',  'equipment_shipping_records:detail', 'equipment_shipping_record_line'])]
    private int $id;

    public function __construct()
    {
        $this->equipmentShippingRecordLines = new ArrayCollection();
        $this->equipmentShippingRecordCosts = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
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

    /**
     * @return Collection<EquipmentShippingRecordLine>
     */
    public function getEquipmentShippingRecordLines()
    {
        return $this->equipmentShippingRecordLines;
    }

    public function addEquipmentShippingRecordLine(EquipmentShippingRecordLine $line): self
    {
        if (!$this->equipmentShippingRecordLines->contains($line)) {
            $this->equipmentShippingRecordLines->add($line);
            $line->equipmentShippingRecord = $this;
        }

        return $this;
    }

    public function removeEquipmentShippingRecordLine(EquipmentShippingRecordLine $line): self
    {
        if ($this->equipmentShippingRecordLines->contains($line)) {
            $this->equipmentShippingRecordLines->removeElement($line);
        }

        return $this;
    }

    /**
     * @return Collection<EquipmentShippingRecordCost>
     */
    public function getEquipmentShippingRecordCosts()
    {
        return $this->equipmentShippingRecordCosts;
    }

    public function addEquipmentShippingRecordCosts(EquipmentShippingRecordCost $cost): self
    {
        $this->equipmentShippingRecordCosts->add($cost);

        return $this;
    }

    public function removeEquipmentShippingRecordCosts(EquipmentShippingRecordCost $cost): self
    {
        if ($this->equipmentShippingRecordCosts->contains($cost)) {
            $this->equipmentShippingRecordCosts->removeElement($cost);
        }

        return $this;
    }

    /**
     * @return Collection<EquipmentShippingRecordFile>
     */
    public function getFiles()
    {
        return $this->files;
    }

    public function addFile(EquipmentShippingRecordFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setEquipmentShippingRecord($this);
        }

        return $this;
    }

    public function removeFile(EquipmentShippingRecordFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
        }

        return $this;
    }

    public function haveToChangeShipAuthorization(): bool
    {
        if ($this->shipAuthorization) {
            foreach ($this->getEquipmentShippingRecordLines() as $equipmentShippingRecordLine) {
                $orderFactory = $equipmentShippingRecordLine->equipmentRecord->orderFactory;
                if (null !== $orderFactory && !$orderFactory->orderLine->isPaymentTermValid) {
                    return true;
                }
            }
        }

        return false;
    }
}
