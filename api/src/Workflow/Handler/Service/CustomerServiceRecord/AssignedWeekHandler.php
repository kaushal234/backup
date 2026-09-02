<?php

declare(strict_types=1);

namespace App\Workflow\Handler\Service\CustomerServiceRecord;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Workflow\Handler\WorkflowHandlerInterface;

class AssignedWeekHandler implements WorkflowHandlerInterface
{
    public function __construct(
    ) {
    }

    /**
     * @param AbstractCustomerServiceRecord $current
     * @param AbstractCustomerServiceRecord $previous
     */
    public function handle(object $current, ?object $previous): bool
    {
        $current->getOpenIntervention()->plannedAt = $current->plannedAt;

        return true;
    }

    public function support(object $current, ?object $previous): bool
    {
        if (null === $previous) {
            return false;
        }

        return
            $current instanceof AbstractCustomerServiceRecord
            && AbstractCustomerServiceRecord::ASSIGNED === $previous->getStatus()
            && $current->plannedAt instanceof \DateTime
            && $current->leader instanceof People
            && $current->getOpenIntervention() instanceof Intervention
        ;
    }
}
