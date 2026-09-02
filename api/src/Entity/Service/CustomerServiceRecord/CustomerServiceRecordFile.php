<?php

declare(strict_types=1);

namespace App\Entity\Service\CustomerServiceRecord;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Entity\File;
use Doctrine\ORM\Mapping as ORM;

#[ApiResource(operations: [new Get()])]
#[ORM\Entity]
#[ORM\Table]
class CustomerServiceRecordFile extends File
{
    #[ORM\ManyToOne(targetEntity: AbstractCustomerServiceRecord::class, inversedBy: 'files')]
    private ?AbstractCustomerServiceRecord $customerServiceRecord = null;

    public function getCustomerServiceRecord(): ?AbstractCustomerServiceRecord
    {
        return $this->customerServiceRecord;
    }

    public function setCustomerServiceRecord(?AbstractCustomerServiceRecord $customerServiceRecord): self
    {
        $this->customerServiceRecord = $customerServiceRecord;

        return $this;
    }
}
