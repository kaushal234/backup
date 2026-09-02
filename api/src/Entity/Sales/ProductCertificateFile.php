<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'product_certificate_files')]
#[App\Loggable(owner: 'certificate', ownerRelation: 'files')]
class ProductCertificateFile extends File
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ProductCertificate', inversedBy: 'files')]
    private ?ProductCertificate $certificate = null;

    public function getCertificate(): ProductCertificate
    {
        return $this->certificate;
    }

    public function setCertificate(ProductCertificate $certificate): self
    {
        $this->certificate = $certificate;

        return $this;
    }
}
