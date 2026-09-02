<?php

declare(strict_types=1);

namespace App\Entity\Manufacturing;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Entity\Directory\Location;
use App\Entity\Sales\Product;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Delete(security: "is_granted('FEATURE_PRODUCT_MANUFACTURING_WRITE')"),
    ],
    routePrefix: 'manufacturing',
    normalizationContext: ['groups' => ['product_manufacturing']],
    denormalizationContext: ['groups' => ['product_manufacturing:write']]
)]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'unique_product_by_factory_by_year', columns: ['product_id', 'factory_id', 'effective_at'])]
#[Loggable]
class ProductManufacturing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['product_manufacturing'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\Product', inversedBy: 'productManufacturings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[MaxDepth(1)]
    #[Groups(['product_manufacturing', 'product_manufacturing:write'])]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['product_manufacturing', 'product_manufacturing:write'])]
    #[ValidLocation(factory: true)]
    private ?Location $factory = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Groups(['product_manufacturing', 'product_manufacturing:write'])]
    private int $industrialIncorporationParameter;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Groups(['product_manufacturing', 'product_manufacturing:write'])]
    private int $factoryStandardEfficiency;

    #[ORM\Column(type: 'datetime')]
    #[Assert\NotNull]
    #[Groups(['product_manufacturing', 'product_manufacturing:write'])]
    private \DateTimeInterface $effectiveAt;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['product_manufacturing', 'product_manufacturing:write'])]
    private ?int $modelBaseHours = null;

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

    public function getFactory(): Location
    {
        return $this->factory;
    }

    public function setFactory(?Location $factory): self
    {
        $this->factory = $factory;

        return $this;
    }

    public function getIndustrialIncorporationParameter(): int
    {
        return $this->industrialIncorporationParameter;
    }

    public function setIndustrialIncorporationParameter(int $industrialIncorporationParameter): self
    {
        $this->industrialIncorporationParameter = $industrialIncorporationParameter;

        return $this;
    }

    public function getFactoryStandardEfficiency(): int
    {
        return $this->factoryStandardEfficiency;
    }

    public function setFactoryStandardEfficiency(int $factoryStandardEfficiency): self
    {
        $this->factoryStandardEfficiency = $factoryStandardEfficiency;

        return $this;
    }

    public function getEffectiveAt(): \DateTimeInterface
    {
        return $this->effectiveAt;
    }

    public function setEffectiveAt(\DateTime $effectiveAt): self
    {
        $this->effectiveAt = $effectiveAt;

        return $this;
    }

    public function getModelBaseHours(): ?int
    {
        return $this->modelBaseHours;
    }

    public function setModelBaseHours(?int $modelBaseHours): self
    {
        $this->modelBaseHours = $modelBaseHours;

        return $this;
    }
}
