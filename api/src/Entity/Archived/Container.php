<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(operations: [])]
#[ORM\Table(name: 'containers')]
class Container
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Archived\ContainerType')]
    #[ORM\JoinColumn(nullable: false)]
    private ContainerType $containerType;

    #[ORM\Column(type: 'integer')]
    private int $quantity;

    #[ORM\Column(type: 'boolean')]
    private bool $loadingAtFactory = false;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Archived\ShippingQuotationRequestLine', inversedBy: 'containers')]
    #[ORM\JoinColumn(nullable: false)]
    private ShippingQuotationRequestLine $shippingQuotationRequestLine;

    public function getId(): int
    {
        return $this->id;
    }

    public function getContainerType(): ContainerType
    {
        return $this->containerType;
    }

    public function setContainerType(ContainerType $containerType): self
    {
        $this->containerType = $containerType;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getShippingQuotationRequestLine(): ShippingQuotationRequestLine
    {
        return $this->shippingQuotationRequestLine;
    }

    public function setShippingQuotationrequestLine(ShippingQuotationRequestLine $shippingQuotationRequestLine): self
    {
        $this->shippingQuotationRequestLine = $shippingQuotationRequestLine;

        return $this;
    }

    public function isLoadingAtFactory(): bool
    {
        return $this->loadingAtFactory;
    }

    public function setLoadingAtFactory(bool $loadingAtFactory): self
    {
        $this->loadingAtFactory = $loadingAtFactory;

        return $this;
    }
}
