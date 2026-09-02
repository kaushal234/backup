<?php

declare(strict_types=1);

namespace App\Workflow\Handler\Service\CustomerServiceRecord;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Workflow\Handler\WorkflowHandlerInterface;
use App\Workflow\WorkflowStatusUpdater;

class AssignedToPendingHandler implements WorkflowHandlerInterface
{
    public function __construct(
        private readonly WorkflowStatusUpdater $workflowStatusUpdater,
    ) {
    }

    /**
     * @param AbstractCustomerServiceRecord $current
     * @param AbstractCustomerServiceRecord $previous
     */
    public function handle(object $current, ?object $previous): bool
    {
        $this->workflowStatusUpdater->applyStatus($current->getOpenIntervention(), Intervention::FAILED_ASSIGNEE);
        $this->workflowStatusUpdater->applyStatus($current, AbstractCustomerServiceRecord::PENDING);

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
            && null === $current->plannedAt
            && null === $current->leader
        ;
    }
}
