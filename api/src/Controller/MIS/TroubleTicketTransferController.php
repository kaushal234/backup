<?php

declare(strict_types=1);

namespace App\Controller\MIS;

use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\MIS\TroubleTicket\Type;
use App\Factory\Common\Notification\MIS\TroubleTicketAssignedNotificationFactory;
use App\Manager\Directory\PeopleManager;
use App\Manager\MIS\TroubleTicket\TroubleTicketManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TroubleTicketTransferController extends AbstractController
{
    public function __construct(
        private readonly TroubleTicketManager $manager,
        private readonly TroubleTicketAssignedNotificationFactory $factory,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(TroubleTicket $troubleTicket, Request $request): TroubleTicket
    {
        if (null === $request->request->get('comment')) {
            throw new BadRequestException('Comment is mandatory.');
        }

        $user = $this->getUser();

        /** @var TroubleTicket $previousData */
        $previousData = $request->attributes->get('previous_data');

        if (true === (bool) $request->request->get('nullifyAssignee')
            && (Type::INCIDENT === $troubleTicket->type->type
                // Case when MOO/GKU/LKU/MIS is validating TTS
                || (Type::REQUEST === $troubleTicket->type->type && TroubleTicket::PENDING_MOO === $troubleTicket->getStatus()
                    && ((null !== $troubleTicket->module->getOperationalOwner() && $user === $troubleTicket->module->getOperationalOwner())
                        || (null !== $troubleTicket->module->getKeyUser() && $user === $troubleTicket->module->getKeyUser())
                        || $troubleTicket->module->getLocalKeyUsers()->contains($user)
                        || ($user instanceof People && PeopleManager::hasGroup($user, 'GG_MIS'))
                    )
                )
                // Case sending back TTS to MIS for an already validated request
                || (Type::REQUEST === $troubleTicket->type->type && TroubleTicket::AWAITING_USER === $troubleTicket->getStatus())
                // Case not approving solution proposed
                || (Type::REQUEST === $troubleTicket->type->type && TroubleTicket::SOLUTION_PROPOSED === $previousData->getStatus())
            )) {
            $troubleTicket->assignee = null;
        }

        if (!\in_array($troubleTicket->getStatus(), TroubleTicket::CLOSED_STATUSES, true) && \in_array($previousData->getStatus(), TroubleTicket::CLOSED_STATUSES, true)) {
            throw new AccessDeniedException('Closed Trouble Ticket cannot be edited');
        }

        if (null !== ($status = $request->request->get('status') ?? $this->manager->getNewStatus())) {
            $troubleTicket->setStatus($status);
        }

        if (\in_array($troubleTicket->getStatus(), [TroubleTicket::SOLUTION_PROPOSED, TroubleTicket::SOLUTION_PROPOSED_MOO], true)) {
            $troubleTicket->solutionProposedAt = new \DateTime();
        }

        if (\in_array($previousData->getStatus(), [TroubleTicket::SOLUTION_PROPOSED, TroubleTicket::SOLUTION_PROPOSED_MOO], true)
            && $previousData->getStatus() !== $troubleTicket->getStatus()
            && !\in_array($troubleTicket->getStatus(), TroubleTicket::CLOSED_STATUSES, true)) {
            $troubleTicket->solutionProposedAt = null;
        }

        if (null !== $troubleTicket->assignee && $troubleTicket->assignee !== $previousData->assignee) {
            $notification = $this->factory->createNotification($troubleTicket, $troubleTicket->assignee);
            $this->entityManager->persist($notification);
        }

        return $troubleTicket;
    }
}
