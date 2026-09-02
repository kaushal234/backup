<?php

declare(strict_types=1);

namespace App\Workflow\Handler\Service\CustomerServiceRecord;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Workflow\Handler\WorkflowHandlerInterface;

class AssignedToAnotherHandler implements WorkflowHandlerInterface
{
    /**
     * @param AbstractCustomerServiceRecord $current
     * @param AbstractCustomerServiceRecord $previous
     */
    public function handle(object $current, ?object $previous): bool
    {
        $current->getOpenIntervention()->leader = $current->leader;

        return true;
    }

    /**
     * @param AbstractCustomerServiceRecord $current
     * @param AbstractCustomerServiceRecord $previous
     */
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
            && $current->leader !== $previous->getOpenIntervention()->leader
        ;
    }
}
