<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [])]
#[ORM\Entity]
#[ORM\Table(name: 'shipping_quotation_request_files')]
class ShippingQuotationRequestFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Archived\ShippingQuotationRequest', inversedBy: 'files')]
    private ?ShippingQuotationRequest $shippingQuotationRequest = null;

    public function getShippingQuotationRequest(): ?ShippingQuotationRequest
    {
        return $this->shippingQuotationRequest;
    }

    public function setShippingQuotationRequest(?ShippingQuotationRequest $shippingQuotationRequest): self
    {
        $this->shippingQuotationRequest = $shippingQuotationRequest;

        return $this;
    }
}
