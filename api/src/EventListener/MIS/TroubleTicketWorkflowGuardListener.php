<?php

declare(strict_types=1);

namespace App\EventListener\MIS;

use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\TransitionBlocker;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class TroubleTicketWorkflowGuardListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.trouble_ticket.guard.from_awaiting_user_to_pending' => ['guardFromAwaitingUserToPending'],
            'workflow.trouble_ticket.guard.from_pending_moo_to_pending' => ['guardFromPendingMooToPending'],
            'workflow.trouble_ticket.guard.to_closed_not_approved' => ['guardToClosedNotApproved'],
            'workflow.trouble_ticket.guard.from_pending_to_in_progress' => ['guardFromPendingToInProgress'],
            'workflow.trouble_ticket.guard.from_awaiting_user_to_in_progress' => ['guardFromAwaitingUserToInProgress'],
            'workflow.trouble_ticket.guard.from_solution_proposed_to_in_progress' => ['guardFromSolutionProposedToInProgress'],
            'workflow.trouble_ticket.guard.to_solution_proposed' => ['guardToSolutionProposed'],
            'workflow.trouble_ticket.guard.from_solution_proposed_to_closed_solved' => ['guardSolutionProposedToClosedResolved'],
            'workflow.trouble_ticket.guard.from_awaiting_user_to_closed_solved' => ['guardAwaitingUserToClosedResolved'],
            'workflow.trouble_ticket.guard.to_closed_not_an_issue' => ['guardToClosedNotAnIssueOrAlreadyRaised'],
            'workflow.trouble_ticket.guard.to_closed_already_raised' => ['guardToClosedNotAnIssueOrAlreadyRaised'],
            'workflow.trouble_ticket.guard.from_closed_to_pending' => ['guardClosedToPendingOrInProgress'],
            'workflow.trouble_ticket.guard.from_closed_to_in_progress' => ['guardClosedToPendingOrInProgress'],
            'workflow.trouble_ticket.guard.to_moo_awaiting_user' => ['guardToMooAwaitingUser'],
            'workflow.trouble_ticket.guard.to_moo_solution_proposed' => ['guardToMooSolutionProposed'],
            'workflow.trouble_ticket.guard.from_solution_proposed_moo_to_pending_moo' => ['guardFromSolutionProposedMooToPendingMoo'],
            'workflow.trouble_ticket.guard.from_awaiting_user_moo_to_pending_moo' => ['guardFromAwaitingUserMooToPendingMoo'],
            'workflow.trouble_ticket.guard.from_solution_proposed_moo_to_closed_solved' => ['guardSolutionProposedMooToClosedResolved'],
            'workflow.trouble_ticket.guard.from_closed_to_pending_moo' => ['guardClosedToPendingMoo'],
            'workflow.trouble_ticket.guard.from_in_progress_to_pending' => ['guardFromInProgressToPending'],
        ];
    }

    public function guardFromAwaitingUserToPending(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->isMooOrKeyUserOrLocalKeyUserOrMIS($troubleTicket) || $this->isAssigneeOrAssignor($troubleTicket)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardFromInProgressToPending(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->isMIS()) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardFromPendingMooToPending(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->isMooOrKeyUserOrLocalKeyUser($troubleTicket) || $this->isMIS()) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardToClosedNotApproved(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->hasAdminClosurePermissions() || $this->isMooOrKeyUserOrLocalKeyUser($troubleTicket)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardFromPendingToInProgress(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->isMIS()) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardFromSolutionProposedToInProgress(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->isAssigneeOrAssignor($troubleTicket)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardFromAwaitingUserToInProgress(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->isAssigneeOrAssignor($troubleTicket) || $this->isMIS()) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardAwaitingUserToClosedResolved(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->hasAdminClosurePermissions() || $this->isAssigneeOrAssignor($troubleTicket)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardToMooAwaitingUser(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->isMooOrKeyUserOrLocalKeyUser($troubleTicket) || $this->isAssigneeOrAssignor($troubleTicket)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardToMooSolutionProposed(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->isMooOrKeyUserOrLocalKeyUser($troubleTicket)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardToSolutionProposed(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->isMIS()) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardFromSolutionProposedMooToPendingMoo(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->isAssigneeOrAssignor($troubleTicket)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardFromAwaitingUserMooToPendingMoo(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->isAssigneeOrAssignor($troubleTicket)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardSolutionProposedMooToClosedResolved(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->hasAdminClosurePermissions()) {
            return;
        }

        $security = $this->serviceLocator->get(Security::class);
        if (null !== ($user = $security->getUser()) && $user instanceof People && $user === $troubleTicket->createdBy) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardSolutionProposedToClosedResolved(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->hasAdminClosurePermissions() || $this->isAssigneeOrAssignor($troubleTicket) || $this->isMooOrKeyUserOrLocalKeyUser($troubleTicket)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardToClosedNotAnIssueOrAlreadyRaised(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        if ($this->hasAdminClosurePermissions() || $this->isMIS() || $this->isAssigneeOrAssignor($troubleTicket)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardClosedToPendingOrInProgress(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        $security = $this->serviceLocator->get(Security::class);
        $request = $this->serviceLocator->get(RequestStack::class);

        /** @var TroubleTicket $previousData */
        $previousData = $request->getCurrentRequest()->attributes->get('previous_data');
        if ($this->isMooOrKeyUserOrLocalKeyUser($troubleTicket) || (null !== ($user = $security->getUser()) && $user instanceof People && $user === $previousData->createdBy)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public function guardClosedToPendingMoo(GuardEvent $event)
    {
        $troubleTicket = $event->getSubject();
        if (!$troubleTicket instanceof TroubleTicket) {
            return;
        }

        $security = $this->serviceLocator->get(Security::class);
        $request = $this->serviceLocator->get(RequestStack::class);

        /** @var TroubleTicket $previousData */
        $previousData = $request->getCurrentRequest()->attributes->get('previous_data');
        if ($this->isMooOrKeyUserOrLocalKeyUser($troubleTicket) || (null !== ($user = $security->getUser()) && $user instanceof People && $user === $previousData->createdBy)) {
            return;
        }

        $event->addTransitionBlocker(new TransitionBlocker('You are not allowed to change the status.', TransitionBlocker::UNKNOWN));
    }

    public static function getSubscribedServices(): array
    {
        return [
            Security::class,
            RequestStack::class,
        ];
    }

    private function isAssigneeOrAssignor(TroubleTicket $troubleTicket): bool
    {
        $security = $this->serviceLocator->get(Security::class);
        $request = $this->serviceLocator->get(RequestStack::class);

        /** @var TroubleTicket $previousData */
        $previousData = $request->getCurrentRequest()->attributes->get('previous_data');
        if (null !== ($user = $security->getUser()) && $user instanceof People
            && ($user === $previousData->assignee || $user === $troubleTicket->createdBy)
        ) {
            return true;
        }

        return false;
    }

    private function isMooOrKeyUserOrLocalKeyUser(TroubleTicket $troubleTicket): bool
    {
        $security = $this->serviceLocator->get(Security::class);
        if (null !== ($user = $security->getUser()) && $user instanceof People
            && ($user === $troubleTicket->module->getKeyUser() || $user === $troubleTicket->module->getOperationalOwner() || $troubleTicket->module->getLocalKeyUsers()->contains($user))
        ) {
            return true;
        }

        return false;
    }

    private function isMIS(): bool
    {
        $security = $this->serviceLocator->get(Security::class);
        if ($security->isGranted('FEATURE_TROUBLE_TICKET_STATUS')) {
            return true;
        }

        return false;
    }

    private function hasAdminClosurePermissions(): bool
    {
        return $this->serviceLocator->get(Security::class)->isGranted('FEATURE_TROUBLE_TICKET_ADMIN_CLOSE');
    }

    private function isMooOrKeyUserOrLocalKeyUserOrMIS(TroubleTicket $troubleTicket): bool
    {
        if ($this->isMooOrKeyUserOrLocalKeyUser($troubleTicket) || $this->isMIS()) {
            return true;
        }

        return false;
    }
}
