<?php

declare(strict_types=1);

namespace App\Entity\Quality\CalibratedTools;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\People;
use App\Entity\Quality\LocationArea;
use App\Entity\UpdatableStatusEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: 'App\Repository\Quality\ToolRepository')]
#[UniqueEntity(fields: ['serialNumber'])]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['tool', 'people_public']]),
        new Post(security: "is_granted('FEATURE_TOOL_WRITE')"),
        new Get(),
        new Put(
            uriTemplate: '/tools/{id}/status',
            denormalizationContext: ['groups' => ['tool:update_status']],
            security: "is_granted('FEATURE_TOOL_WRITE')",
            validate: false,
            name: 'tool_status',
        ),
        new Put(security: "is_granted('FEATURE_TOOL_WRITE')"),
        new Delete(security: "is_granted('FEATURE_TOOL_WRITE')"),
    ],
    routePrefix: 'quality/calibrated_tools',
    normalizationContext: ['groups' => ['tool_detail', 'people_public', 'file:light']],
    denormalizationContext: ['groups' => ['tool_write']],
)]
#[ApiFilter(GroupFilter::class, id: 'override', arguments: ['parameterName' => 'normalizationGroupsOverride', 'overrideDefaultGroups' => true, 'whitelist' => ['tool_export']])]
#[ApiFilter(OrderFilter::class)]
#[ApiFilter(SearchFilter::class, properties: ['description' => 'partial', 'serialNumber' => 'partial', 'locationArea' => 'exact', 'locationArea.supervisor' => 'exact', 'locationArea.factory' => 'exact', 'calibrationInterval' => 'exact', 'createdBy' => 'exact', 'calibrationNotice' => 'exact', 'vendorId' => 'exact', 'vendorErp' => 'partial', 'vendorName' => 'partial', 'status' => 'exact', 'toolType', 'calibrationLogs' => 'exact'])]
#[App\Loggable]
#[Gedmo\SoftDeleteable]
class Tool implements UpdatableStatusEntityInterface
{
    /**
     * @var string
     */
    final public const ACTIVE = 'ACTIVE';
    /**
     * @var string
     */
    final public const CALIBRATION_DUE_SOON = 'CALIBRATION_DUE_SOON';
    /**
     * @var string
     */
    final public const EXPIRED = 'EXPIRED';
    /**
     * @var string
     */
    final public const UNDER_CALIBRATION = 'UNDER_CALIBRATION';
    /**
     * @var string
     */
    final public const OUT_OF_SERVICE = 'OUT_OF_SERVICE';
    /**
     * @var string
     */
    final public const SCRAPPED = 'SCRAPPED';

    #[Groups(['tool', 'tool_detail'])]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['tool', 'tool_detail', 'tool_write', 'tool_export'])]
    #[ORM\Column(name: 'serial_number', type: 'string', length: 255, unique: true, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 255)]
    private ?string $serialNumber = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['tool', 'tool_detail', 'tool_write', 'tool_export'])]
    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    private ?string $description = null;

    #[ApiProperty(iris: ['https://schema.org/Integer'])]
    #[Groups(['tool', 'tool_detail', 'tool_write'])]
    #[ORM\Column(name: 'calibration_interval', type: 'integer', nullable: true)]
    #[Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}.')]
    #[Assert\Range(invalidMessage: 'The number provided is not a valid number of days.', min: 1, max: 99999999999)]
    private ?int $calibrationInterval = null;

    #[ApiProperty(iris: ['https://schema.org/Integer'])]
    #[Groups(['tool', 'tool_detail', 'tool_write'])]
    #[ORM\Column(name: 'calibration_notice', type: 'integer', nullable: true)]
    #[Assert\Type(type: 'integer', message: 'The value {{ value }} is not a valid {{ type }}.')]
    #[Assert\Range(invalidMessage: 'The number provided is not a valid number of days.', min: 1, max: 99999999999)]
    private ?int $calibrationNotice = null;

    #[Groups(['tool_detail', 'tool_write'])]
    #[ApiProperty(iris: ['https://schema.org/Date'])]
    #[ORM\Column(name: 'purchasing_date', type: 'datetime')]
    #[Assert\NotBlank]
    #[Assert\Type(type: 'datetime')]
    private \DateTimeInterface $purchasingDate;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Assert\AtLeastOneOf([new Assert\Blank(), new Assert\Length(min: 1, max: 255)])]
    #[Groups(['tool_detail', 'tool_write'])]
    #[ORM\Column(name: 'vendor_id', type: 'string', length: 255, nullable: true)]
    private ?string $vendorId = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Assert\AtLeastOneOf([new Assert\Blank(), new Assert\Length(min: 1, max: 255)])]
    #[Groups(['tool_detail', 'tool_write'])]
    #[ORM\Column(name: 'vendor_erp', type: 'string', length: 255, nullable: true)]
    private ?string $vendorErp = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Assert\AtLeastOneOf([new Assert\Blank(), new Assert\Length(min: 1, max: 255)])]
    #[Groups(['tool_detail', 'tool_write'])]
    #[ORM\Column(name: 'vendor_name', type: 'string', length: 255, nullable: true)]
    private ?string $vendorName = null;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['tool', 'tool_detail', 'tool_export', 'tool:update_status'])]
    #[ORM\Column(name: 'status', type: 'string', length: 25)]
    #[Assert\Choice(choices: [self::ACTIVE, self::CALIBRATION_DUE_SOON, self::EXPIRED, self::UNDER_CALIBRATION, self::OUT_OF_SERVICE, self::SCRAPPED])]
    #[Assert\NotNull]
    private string $status = self::OUT_OF_SERVICE;

    /**
     * Many tools have one tool type.
     */
    #[Groups(['tool', 'tool_detail', 'tool_write'])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\CalibratedTools\ToolType', inversedBy: 'tools')]
    #[Assert\NotBlank]
    private ?ToolType $toolType = null;

    /**
     * Many tools have one people.
     */
    #[Groups(['tool', 'tool_detail'])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'created_by')]
    #[Gedmo\Blameable(on: 'create')]
    private ?People $createdBy = null;

    /**
     * Many tools have one location area.
     */
    #[ORM\ManyToOne(targetEntity: '\App\Entity\Quality\LocationArea', inversedBy: 'tools')]
    #[ORM\JoinColumn(name: 'location_area_id', nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['tool', 'tool_detail', 'tool_write'])]
    private LocationArea $locationArea;

    /**
     * one tool has many logs.
     *
     * @var ArrayCollection<CalibrationLog>
     */
    #[Groups(['tool_detail', 'tool_write'])]
    #[ORM\OneToMany(mappedBy: 'tool', targetEntity: 'App\Entity\Quality\CalibratedTools\CalibrationLog', cascade: ['all'])]
    private Collection $calibrationLogs;

    #[ORM\Column(name: 'deletedAt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    #[Assert\NotNull]
    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['tool', 'tool_detail', 'tool_write', 'tool_export'])]
    private ?\DateTime $nextCalibrationDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['tool', 'tool_detail', 'tool_export'])]
    #[Gedmo\Timestampable(on: 'change', field: 'status')]
    private ?\DateTime $statusUpdatedAt = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->calibrationLogs = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): self
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCalibrationInterval(): ?int
    {
        return $this->calibrationInterval;
    }

    public function setCalibrationInterval(?int $calibrationInterval): self
    {
        $this->calibrationInterval = $calibrationInterval;

        return $this;
    }

    public function getCalibrationNotice(): ?int
    {
        return $this->calibrationNotice;
    }

    public function setCalibrationNotice(int $calibrationNotice): self
    {
        $this->calibrationNotice = $calibrationNotice;

        return $this;
    }

    public function getPurchasingDate(): ?\DateTimeInterface
    {
        return $this->purchasingDate;
    }

    public function setPurchasingDate(\DateTime $purchasingDate): self
    {
        $this->purchasingDate = $purchasingDate;

        return $this;
    }

    public function getVendorId(): ?string
    {
        return $this->vendorId;
    }

    public function setVendorId(?string $vendorId): self
    {
        $this->vendorId = $vendorId;

        return $this;
    }

    public function getVendorErp(): ?string
    {
        return $this->vendorErp;
    }

    public function setVendorErp(?string $vendorErp): self
    {
        $this->vendorErp = $vendorErp;

        return $this;
    }

    public function getVendorName(): ?string
    {
        return $this->vendorName;
    }

    public function setVendorName(?string $vendorName): self
    {
        $this->vendorName = $vendorName;

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

    public function getToolType(): ToolType
    {
        return $this->toolType;
    }

    public function setToolType(ToolType $toolType): self
    {
        $this->toolType = $toolType;

        return $this;
    }

    public function getCreatedBy(): People
    {
        return $this->createdBy;
    }

    public function setCreatedBy(People $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getLocationArea(): LocationArea
    {
        return $this->locationArea;
    }

    public function setLocationArea(LocationArea $locationArea): self
    {
        $this->locationArea = $locationArea;

        return $this;
    }

    public function getCalibrationLogs(): Collection
    {
        return $this->calibrationLogs;
    }

    public function addCalibrationLog(CalibrationLog $calibrationLog)
    {
        $this->calibrationLogs->add($calibrationLog);

        return $this;
    }

    public function removeCalibrationLog(CalibrationLog $calibrationLog)
    {
        $this->calibrationLogs->removeElement($calibrationLog);

        return $this;
    }

    public function getDeletedAt(): \DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getNextCalibrationDate(): ?\DateTime
    {
        return $this->nextCalibrationDate;
    }

    public function setNextCalibrationDate(\DateTime $nextCalibrationDate): self
    {
        $this->nextCalibrationDate = $nextCalibrationDate;

        return $this;
    }

    public function getStatusUpdatedAt(): ?\DateTime
    {
        return $this->statusUpdatedAt;
    }

    public function setStatusUpdatedAt(?\DateTime $statusUpdatedAt): self
    {
        $this->statusUpdatedAt = $statusUpdatedAt;

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (null === $this->calibrationInterval && null === $this->nextCalibrationDate) {
            $context
                ->buildViolation('You should fill this field')
                ->atPath('calibrationInterval')
                ->addViolation()
            ;
        }

        if (
            $this->nextCalibrationDate < new \DateTime()
            && !\in_array($this->getStatus(), [self::SCRAPPED, self::OUT_OF_SERVICE, self::UNDER_CALIBRATION], true)
        ) {
            $context
                ->buildViolation('This value should be in the future')
                ->atPath('nextCalibrationDate')
                ->addViolation()
            ;
        }
    }
}
