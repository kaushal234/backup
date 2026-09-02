<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\AbstractTag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/sales/product_family_tags'),
        new Get(uriTemplate: '/sales/product_family_tags/{id}'),
    ],
    normalizationContext: ['groups' => ['tag', 'people_public', 'expose_legacy']],
    denormalizationContext: ['groups' => ['tag_write']]
)]
#[ORM\Table(name: 'product_families_tags')]
class ProductFamilyTag extends AbstractTag
{
    /**
     * @var Collection<ProductFamily>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Sales\ProductFamily', inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'product_families_tags_xref')]
    private Collection $productFamilies;

    public function __construct()
    {
        $this->productFamilies = new ArrayCollection();
    }

    /**
     * @return Collection<ProductFamily>
     */
    public function getProductFamilies(): Collection
    {
        return $this->productFamilies;
    }

    public function addProductFamily(ProductFamily $productFamily): self
    {
        $this->productFamilies->add($productFamily);

        return $this;
    }

    public function removeProductFamily(ProductFamily $productFamily): self
    {
        $this->productFamilies->removeElement($productFamily);

        return $this;
    }
}
