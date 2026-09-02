<?php

declare(strict_types=1);

namespace App\Entity\Manufacturing;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\Directory\Location;
use App\Entity\Sales\Product;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity(repositoryClass: 'App\Repository\Manufacturing\ProductStandardItemRepository')]
class ProductStandardItem
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['catalogue_product_detail'])]
    private int $id;

    #[ORM\Column(type: 'string', length: 30)]
    #[Assert\Length(max: 30)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['catalogue_product_detail', 'catalogue_product_write', 'product_standard_item_write'])]
    private string $standardItem;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Product', inversedBy: 'productStandardItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['catalogue_product_detail', 'catalogue_product_write', 'product_standard_item_write'])]
    #[ValidLocation(factory: true)]
    private Location $factory;

    public function getId(): int
    {
        return $this->id;
    }

    public function getStandardItem(): string
    {
        return $this->standardItem;
    }

    public function setStandardItem(string $standardItem): self
    {
        $this->standardItem = $standardItem;

        return $this;
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

    public function getFactory(): Location
    {
        return $this->factory;
    }

    public function setFactory(Location $factory): self
    {
        $this->factory = $factory;

        return $this;
    }
}
