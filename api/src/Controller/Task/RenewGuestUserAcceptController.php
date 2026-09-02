<?php

declare(strict_types=1);

namespace App\Controller\Task;

use App\Entity\Directory\People;
use App\Entity\Task\RenewGuestUser;
use App\Entity\Task\Task;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class RenewGuestUserAcceptController extends AbstractController
{
    public function __invoke(RenewGuestUser $task, Request $request, #[CurrentUser] People $user): RenewGuestUser
    {
        if (Task::CLOSED === $task->getStatus()) {
            throw $this->createAccessDeniedException('This task is already closed.');
        }

        $task->renewalDecision = RenewGuestUser::RENEWAL_YES;
        $task->setStatus(Task::CLOSED);

        /** @var \DateTime $plannedDisableDate */
        $plannedDisableDate = clone $task->guestUser->getPlannedDisableAt();
        $plannedDisableDate->add(new \DateInterval(\sprintf('P%dM', $task->renewalDurationMonths)));
        $task->guestUser->setPlannedDisableAt($plannedDisableDate);

        return $task;
    }
}
