<?php

declare(strict_types=1);

namespace App\Factory\Service;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use Symfony\Bundle\SecurityBundle\Security;

class InterventionFactory
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public function createFromCustomerServiceRecord(AbstractCustomerServiceRecord $customerServiceRecord): Intervention
    {
        /** @var People $user */
        $user = $this->security->getUser();

        $intervention = new Intervention();
        $intervention->leader = $customerServiceRecord->leader;
        $intervention->customerServiceRecord = $customerServiceRecord;
        $intervention->plannedAt = $customerServiceRecord->plannedAt;
        $intervention->plannedBy = $user;

        return $intervention;
    }
}
