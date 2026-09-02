<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Controller\File\DownloadController;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: 'App\Repository\Parts\TrackingRepository')]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Get(
            uriTemplate: '/trackings/{id}/files/{fileId}',
            uriVariables: [
                'fileId' => new Link(toProperty: 'files', fromClass: TrackingFile::class),
                'id' => new Link(fromClass: Tracking::class),
            ],
            defaults: ['parentProperty' => 'tracking', 'class' => TrackingFile::class],
            controller: DownloadController::class,
            security: "is_granted('FEATURE_TRACKING_WRITE') or is_granted('MOO_SPR')",
            name: 'download_parts_file',
        ),
    ],
    routePrefix: 'parts',
    normalizationContext: ['groups' => ['tracking', 'courier', 'file', 'location_public', 'people_public'], 'datetime_format' => 'Y-m-d'],
    denormalizationContext: ['groups' => []],
)]
#[ORM\Table(name: 'trackings')]
#[ApiFilter(SearchFilter::class, properties: ['location', 'packingSlip', 'trackingNumber', 'salesOrder'])]
#[ApiFilter(DateFilter::class, properties: ['deliveryDate'])]
#[Legacy\Synchronize(table: 'erp_dino_trno')]
class Tracking implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    /** @var string */
    final public const AWB = 'AWB';

    /** @var string */
    final public const BOL = 'BOL';

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['tracking'])]
    #[Gedmo\Blameable(on: 'create')]
    public People $createdBy;

    #[ORM\Column(type: 'datetime')]
    #[Groups(['tracking'])]
    #[Legacy\Column(column: 'dt', transformer: DateTimeToString::class)]
    #[Gedmo\Timestampable(on: 'create')]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['tracking'])]
    #[Legacy\Column(column: 'trno')]
    #[Legacy\Column(column: 'trnoBAK')]
    public string $trackingNumber;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['tracking'])]
    #[ValidLocation(erpInLN: true)]
    #[Legacy\Column(column: 'erp', transformer: ObjectToProperty::class, options: ['property' => 'erp'])]
    public Location $location;

    #[ORM\Column(type: 'string', length: 9, nullable: true)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['tracking'])]
    public ?string $salesOrder = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Groups(['tracking'])]
    #[Legacy\Column(column: 'dino')]
    public int $packingSlip;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Parts\Courier')]
    #[Groups(['tracking'])]
    #[Legacy\Column(column: 'courier', transformer: ObjectToProperty::class, options: ['property' => 'name', 'nullValue' => self::AWB])]
    public ?Courier $courier = null;

    #[ORM\Column(name: 'delivery_date', type: 'datetime', nullable: true)]
    #[Groups(['tracking'])]
    public ?\DateTimeInterface $deliveryDate = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Groups(['tracking'])]
    public ?string $description = null;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Choice(choices: [self::AWB, self::BOL])]
    #[Groups(['tracking'])]
    public ?string $documentType = null;

    /**
     * @var Collection<TrackingFile>
     */
    #[ORM\OneToMany(mappedBy: 'tracking', targetEntity: 'App\Entity\Parts\TrackingFile', cascade: ['persist'], orphanRemoval: true)]
    private Collection $files;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['tracking'])]
    private int $id;

    public function __construct()
    {
        $this->files = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    #[Groups(['tracking'])]
    public function getDocument(): ?TrackingFile
    {
        if (0 === $this->files->count()) {
            return null;
        }

        return $this->files->first();
    }

    public function setDocument(?TrackingFile $file): self
    {
        if (null === $file) {
            $this->files = new ArrayCollection();

            return $this;
        }

        return $this->addFile($file);
    }

    public function addFile(TrackingFile $file): self
    {
        $file->tracking = $this;
        $this->files->add($file);

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (null === $this->courier && null === $this->documentType) {
            $context->buildViolation('Either courier or document type should be set.')
                ->atPath('courier')
                ->addViolation();
        }
    }
}
