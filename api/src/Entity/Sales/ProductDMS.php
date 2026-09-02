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
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    shortName: 'productDms',
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_CATALOG_PRODUCT_ADD_DMS') or is_granted('MOO_CAT')"),
        new Get(),
        new Delete(security: "is_granted('FEATURE_CATALOG_CREATE') or is_granted('MOO_CAT')"),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['document', 'catalogue_product_dms', 'expose_legacy', 'catalogue_product', 'family', 'location_public', 'people_public']],
    denormalizationContext: ['groups' => ['catalogue_product_dms_write']]
)]
#[ORM\Table]
#[ApiFilter(OrderFilter::class, properties: ['dms', 'id'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['product.name' => 'partial'])]
#[ApiFilter(SearchFilter::class, properties: ['product' => 'exact', 'dmsType' => 'exact', 'legacyId' => 'exact'])]
#[App\Loggable]
class ProductDMS
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['catalogue_product_dms'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Product', inversedBy: 'productDMS')]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['catalogue_product_dms', 'catalogue_product_dms_write'])]
    private ?Product $product = null;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\DMS')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['catalogue_product_dms', 'catalogue_product_dms_write', 'catalogue_type_detail'])]
    private DMS $dms;

    #[ORM\Column(type: 'string', nullable: true)]
    #[Assert\Type(type: 'string')]
    #[Assert\Choice(choices: [ProductFamilyDMS::SPECS, ProductFamilyDMS::PHOTOS, ProductFamilyDMS::DATASHEET, ProductFamilyDMS::PRESENTATION, ProductFamilyDMS::CONFIGURATOR, ''])]
    #[Groups(['catalogue_product_dms', 'catalogue_product_dms_write', 'catalogue_type_detail'])]
    private ?string $dmsType = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

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
