<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table(name: 'supplier_corrective_action_request_files')]
#[App\Loggable(owner: 'supplierCorrectiveActionRequest', ownerRelation: 'files')]
class SupplierCorrectiveActionRequestFile extends File
{
    #[ApiProperty(iris: ['https://schema.org/Boolean'])]
    #[Groups(['file', 'file_public_write'])]
    protected bool $public = true;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\SupplierCorrectiveActionRequest', inversedBy: 'files')]
    private ?SupplierCorrectiveActionRequest $supplierCorrectiveActionRequest = null;

    public function getSupplierCorrectiveActionRequest(): ?SupplierCorrectiveActionRequest
    {
        return $this->supplierCorrectiveActionRequest;
    }

    public function setSupplierCorrectiveActionRequest(?SupplierCorrectiveActionRequest $supplierCorrectiveActionRequest): self
    {
        $this->supplierCorrectiveActionRequest = $supplierCorrectiveActionRequest;

        return $this;
    }
}
