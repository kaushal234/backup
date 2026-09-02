<?php

declare(strict_types=1);

namespace App\Entity\Parts;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity]
#[ApiResource(operations: [new Get()], routePrefix: 'parts', normalizationContext: [], denormalizationContext: [])]
#[ORM\Table(name: 'supplier_corrective_action_requests_parts')]
class SupplierCorrectiveActionRequestPart extends Part
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\SupplierCorrectiveActionRequest', inversedBy: 'parts')]
    #[ORM\JoinColumn(nullable: false)]
    #[MaxDepth(1)]
    public SupplierCorrectiveActionRequest $supplierCorrectiveActionRequest;

    public function __construct()
    {
        $this->quantity = 1;
    }
}
