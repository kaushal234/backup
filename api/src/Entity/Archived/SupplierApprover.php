<?php

declare(strict_types=1);

namespace App\Entity\Archived;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(operations: [])]

class SupplierApprover extends AbstractApprover
{
    #[ORM\Column(type: 'string')]
    public string $supplier;

    #[ORM\Column(type: 'string')]
    public ?string $supplierName = null;

    public function getBusinessPartnerCode(): ?string
    {
        return $this->getSupplierNumber();
    }

    public function getSupplierNumber(): ?string
    {
        return $this->supplier;
    }

    public function setSupplierName(string $name): ?self
    {
        $this->supplierName = $name;

        return $this;
    }
}
