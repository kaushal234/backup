<?php

declare(strict_types=1);

namespace App\Workflow\Handler\Service\CustomerServiceRecord;

use App\Entity\Directory\People;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Factory\Service\InterventionFactory;
use App\Workflow\Handler\WorkflowHandlerInterface;
use App\Workflow\WorkflowStatusUpdater;

class PlannedToAssignedHandler implements WorkflowHandlerInterface
{
    public function __construct(
        private readonly WorkflowStatusUpdater $workflowStatusUpdater,
        private readonly InterventionFactory $interventionFactory,
    ) {
    }

    /**
     * @param AbstractCustomerServiceRecord $current
     * @param AbstractCustomerServiceRecord $previous
     */
    public function handle(object $current, ?object $previous): bool
    {
        $intervention = $this->interventionFactory->createFromCustomerServiceRecord($current);
        $current->addIntervention($intervention);
        $this->workflowStatusUpdater->applyStatus($current, AbstractCustomerServiceRecord::ASSIGNED);

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
            && $current->plannedAt instanceof \DateTime
            && $current->leader instanceof People
        ;
    }
}
