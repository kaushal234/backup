<?php

declare(strict_types=1);

namespace App\EventListener\MIS;

use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class TroubleTicketWorkflowCompletedListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.trouble_ticket.completed.to_solution_proposed' => ['completedToSolutionProposed'],
            'workflow.trouble_ticket.completed.to_moo_solution_proposed' => ['completedToSolutionProposed'],
            'workflow.trouble_ticket.completed.from_solution_proposed_to_in_progress' => ['completedFromSolutionProposed'],
            'workflow.trouble_ticket.completed.from_solution_proposed_moo_to_pending_moo' => ['completedFromSolutionProposed'],
            'workflow.trouble_ticket.completed.from_in_progress_to_pending' => ['fromInProgressToPending'],
        ];
    }

    public function completedToSolutionProposed(CompletedEvent $event): void
    {
        /** @var TroubleTicket $troubleTicket */
        $troubleTicket = $event->getSubject();
        $troubleTicket->solutionProposedAt = new \DateTime();

        // Assign MIS user if no mis assignee.
        $user = $this->serviceLocator->get(Security::class)->getUser();
        if (null === $troubleTicket->misAssignee && $user instanceof People) {
            $troubleTicket->misAssignee = $user;
        }

        $this->recomputeEntity($troubleTicket);
    }

    public function completedFromSolutionProposed(CompletedEvent $event): void
    {
        /** @var TroubleTicket $troubleTicket */
        $troubleTicket = $event->getSubject();
        $troubleTicket->solutionProposedAt = null;
        $troubleTicket->autoEscalated = false;
        $troubleTicket->autoEscalatedAt = null;
        $this->recomputeEntity($troubleTicket);
    }

    public function fromInProgressToPending(CompletedEvent $event): void
    {
        /** @var TroubleTicket $troubleTicket */
        $troubleTicket = $event->getSubject();
        $troubleTicket->misAssignee = null;
        $this->recomputeEntity($troubleTicket);
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            Security::class,
        ];
    }

    /**
     * It seems updating entity in a workflow event and linked to a mapped superclass is not working.
     * The status is properly changed on entity but not persisted.
     * This method forces Doctrine to recalculate changes.
     */
    protected function recomputeEntity(TroubleTicket $troubleTicket): void
    {
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet(
            $entityManager->getClassMetadata(TroubleTicket::class),
            $troubleTicket
        );
    }
}
