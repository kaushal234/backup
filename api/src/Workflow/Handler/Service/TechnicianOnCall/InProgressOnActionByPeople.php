<?php

declare(strict_types=1);

namespace App\Workflow\Handler\Service\TechnicianOnCall;

use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use App\Workflow\Handler\WorkflowHandlerInterface;
use App\Workflow\WorkflowStatusUpdater;
use Symfony\Bundle\SecurityBundle\Security;

class InProgressOnActionByPeople implements WorkflowHandlerInterface
{
    public function __construct(
        private readonly WorkflowStatusUpdater $workflowStatusUpdater,
        private readonly Security $security
    ) {
    }

    /**
     * @param TechnicianOnCall $current
     * @param TechnicianOnCall $previous
     */
    public function handle(object $current, ?object $previous): bool
    {
        $this->workflowStatusUpdater->applyStatus($current, TechnicianOnCall::IN_PROGRESS);

        return true;
    }

    public function support(object $current, ?object $previous): bool
    {
        return
            $current instanceof TechnicianOnCall
            && (TechnicianOnCall::PENDING === $current->getStatus() || TechnicianOnCall::SUSPENDED === $current->getStatus())
            && $this->security->getUser() instanceof People
        ;
    }
}
