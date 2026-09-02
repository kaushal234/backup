<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Controller\Support\ManualPrintController;
use App\Entity\Directory\People;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\DateTimeToString;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_PRINTER_WRITE')"),
        new Get(),
        new Get(
            uriTemplate: '/manual_prints/{id}',
            formats: ['zip' => 'application/zip'],
            routePrefix: 'public/support',
            controller: ManualPrintController::class,
            security: "is_granted('PUBLIC_ACCESS')",
            name: 'manual_print_zip',
        ),
    ],
    routePrefix: 'support',
    normalizationContext: ['groups' => ['manual_print', 'address', 'manual_public', 'equipment_record_detail', 'printer:detail', 'expose_legacy', 'people_public']],
    denormalizationContext: ['groups' => ['manual_print:write', 'manual:write', 'printer:write']]
)]
#[ORM\Table(name: 'manual_prints')]
#[Legacy\Synchronize(table: 'manuals_downloads')]
class ManualPrint implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Support\Manual', inversedBy: 'prints')]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Legacy\Column(column: 'er_id', transformer: ObjectToProperty::class, options: ['property' => 'equipmentRecord.legacyId', 'nullValue' => 0])]
    #[Groups(['manual_print', 'manual:write'])]
    #[Assert\NotNull]
    #[MaxDepth(1)]
    public Manual $manual;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Support\ManualPrinter', inversedBy: 'prints')]
    #[Assert\NotNull]
    #[Groups(['manual_print', 'printer:write'])]
    #[MaxDepth(1)]
    public ManualPrinter $manualPrinter;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Groups(['manual_print', 'manual_print:write'])]
    #[Legacy\Column(column: 'std_manual')]
    public int $standard = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Groups(['manual_print', 'manual_print:write'])]
    #[Legacy\Column(column: 'full_manual')]
    public int $full = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Groups(['manual_print', 'manual_print:write'])]
    #[Legacy\Column(column: 'extra_cd')]
    public int $extra = 0;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(value: 0)]
    #[Groups(['manual_print', 'manual_print:write'])]
    #[Legacy\Column(column: 'chapter_5')]
    public int $chapter5 = 0;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(['manual_print', 'manual_print:write'])]
    #[Legacy\Column(column: 'comment', transformer: Utf8ToHtmlEntities::class)]
    public ?string $comment = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['manual_print'])]
    #[Legacy\Column(column: 'dt_entered', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    #[Gedmo\Timestampable(on: 'create')]
    public ?\DateTime $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Gedmo\Timestampable(on: 'update')]
    public ?\DateTime $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    #[Legacy\Column(column: 'poster_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    #[Gedmo\Blameable(on: 'create')]
    #[Groups(['manual_print'])]
    public ?People $createdBy;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id')]
    #[Gedmo\Blameable(on: 'update')]
    public ?People $updatedBy = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['manual_print', 'manual_print:write'])]
    #[Legacy\Column(column: 'dt_delivery', transformer: DateTimeToString::class, options: ['format' => 'Y-m-d'])]
    public ?\DateTime $requestedDeliveryDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['manual_print'])]
    public ?\DateTime $downloadedAt = null;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[Groups(['manual_print'])]
    private Uuid $id;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    // this setter is only used by fixtures for behat tests
    public function setId(?string $uuid): self
    {
        $this->id = Uuid::fromString($uuid);

        return $this;
    }
}
