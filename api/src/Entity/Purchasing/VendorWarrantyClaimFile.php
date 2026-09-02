<?php

declare(strict_types=1);

namespace App\Entity\Purchasing;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'vendor_warranty_claim_files')]
#[App\Loggable(owner: 'vendorWarrantyClaim', ownerRelation: 'files')]
class VendorWarrantyClaimFile extends File
{
    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[Groups(['file', 'file_public_write'])]
    protected bool $public = false;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Purchasing\VendorWarrantyClaim', inversedBy: 'files')]
    private ?VendorWarrantyClaim $vendorWarrantyClaim = null;

    public function getVendorWarrantyClaim(): ?VendorWarrantyClaim
    {
        return $this->vendorWarrantyClaim;
    }

    public function setVendorWarrantyClaim(?VendorWarrantyClaim $vendorWarrantyClaim): self
    {
        $this->vendorWarrantyClaim = $vendorWarrantyClaim;

        return $this;
    }
}
