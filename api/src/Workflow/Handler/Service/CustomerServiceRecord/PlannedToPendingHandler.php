<?php

declare(strict_types=1);

namespace App\Workflow\Handler\Service\CustomerServiceRecord;

use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Workflow\Handler\WorkflowHandlerInterface;
use App\Workflow\WorkflowStatusUpdater;

class PlannedToPendingHandler implements WorkflowHandlerInterface
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
        $this->workflowStatusUpdater->applyStatus($current, AbstractCustomerServiceRecord::PENDING);

        return true;
    }

    public function support(object $current, ?object $previous): bool
    {
        if (null === $previous) {
            return false;
        }

        return
            $current instanceof AbstractCustomerServiceRecord
            && AbstractCustomerServiceRecord::PLANNED === $previous->getStatus()
            && null === $current->plannedAt
            && null === $current->leader
        ;
    }
}
