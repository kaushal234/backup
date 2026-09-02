<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\DMS;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use LegacyBundle\Doctrine\Transformer\ObjectToProperty;
use LegacyBundle\Entity\LegacyIdentifierTrait;
use LegacyBundle\Entity\LegacyIdInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    shortName: 'productFamilyDms',
    operations: [
        new GetCollection(security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')"),
        new Post(security: "is_granted('FEATURE_CATALOG_FAMILY_ADD_DMS') or is_granted('MOO_CAT')"),
        new Get(),
        new Delete(security: "is_granted('FEATURE_CATALOG_CREATE') or is_granted('MOO_CAT')"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['catalogue_family_dms', 'expose_legacy', 'catalogue_family', 'catalogue_type', 'family', 'people_public', 'document']],
    denormalizationContext: ['groups' => ['catalogue_family_dms_write']]
)]
#[ORM\Table]
#[ApiFilter(OrderFilter::class, properties: ['id'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['family.name' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: ['dms' => 'exact', 'family' => 'exact', 'dmsType' => 'exact', 'legacyId' => 'exact'])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'products_datasheets_dms')]
class ProductFamilyDMS implements LegacyIdInterface
{
    use LegacyIdentifierTrait;

    /**
     * @var string
     */
    final public const SPECS = 'Specs';

    /**
     * @var string
     */
    final public const PHOTOS = 'Photos';

    /**
     * @var string
     */
    final public const DATASHEET = 'Datasheet';

    /**
     * @var string
     */
    final public const CONFIGURATOR = 'Configurator';

    /**
     * @var string
     */
    final public const PRESENTATION = 'Presentation';

    /**
     * @var string
     */
    final public const LINE_DRAWING = 'Line Drawing';

    /**
     * @var string
     */
    final public const FACT_SHEET = 'Fact Sheet';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['catalogue_family_dms'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ProductFamily', inversedBy: 'productFamilyDMS')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['catalogue_family_dms', 'catalogue_family_dms_write'])]
    #[Legacy\Column(column: 'parent_id', transformer: ObjectToProperty::class, options: ['property' => 'legacy_id'])]
    private ProductFamily $family;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\DMS')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['catalogue_family_dms', 'catalogue_family_dms_write', 'catalogue_type_detail'])]
    #[Legacy\Column(column: 'dms_id', transformer: ObjectToProperty::class, options: ['property' => 'legacyId'])]
    private DMS $dms;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Choice(choices: [self::SPECS, self::PHOTOS, self::DATASHEET, self::PRESENTATION, self::CONFIGURATOR, self::LINE_DRAWING, self::FACT_SHEET, ''])]
    #[Groups(['catalogue_family_dms', 'catalogue_family_dms_write', 'catalogue_type_detail'])]
    #[Legacy\Column(column: 'type')]
    private ?string $dmsType = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFamily(): ProductFamily
    {
        return $this->family;
    }

    public function setFamily(ProductFamily $family): self
    {
        $this->family = $family;

        return $this;
    }

    public function getDms(): DMS
    {
        return $this->dms;
    }

    public function setDms(DMS $dms): self
    {
        $this->dms = $dms;

        return $this;
    }

    public function getDmsType(): ?string
    {
        return $this->dmsType;
    }

    public function setDmsType(?string $dmsType): self
    {
        $this->dmsType = $dmsType;

        return $this;
    }
}
