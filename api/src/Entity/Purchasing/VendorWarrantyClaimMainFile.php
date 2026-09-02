<?php

declare(strict_types=1);

namespace App\Entity\Purchasing;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'vendor_warranty_claim_main_files')]
#[App\Loggable(owner: 'vendorWarrantyClaim', ownerRelation: 'mainFiles')]
class VendorWarrantyClaimMainFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Purchasing\WCVendorWarrantyClaim', inversedBy: 'mainFiles')]
    private ?WCVendorWarrantyClaim $vendorWarrantyClaim = null;

    public function getVendorWarrantyClaim(): ?WCVendorWarrantyClaim
    {
        return $this->vendorWarrantyClaim;
    }

    public function setVendorWarrantyClaim(?WCVendorWarrantyClaim $vendorWarrantyClaim): self
    {
        $this->vendorWarrantyClaim = $vendorWarrantyClaim;

        return $this;
    }
}
