<?php

declare(strict_types=1);

namespace App\Controller\MIS;

use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\MIS\TroubleTicket\Type;
use App\Manager\MIS\TroubleTicket\TroubleTicketManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class TroubleTicketReopenController extends AbstractController
{
    public function __construct(
        private readonly TroubleTicketManager $manager,
    ) {
    }

    public function __invoke(TroubleTicket $troubleTicket, Request $request): TroubleTicket
    {
        if (null === $request->request->get('comment')) {
            throw new BadRequestException('Comment is mandatory.');
        }

        $status = match (true) {
            Type::REQUEST === $troubleTicket->type->type => TroubleTicket::PENDING_MOO,
            Type::INCIDENT === $troubleTicket->type->type && null !== $troubleTicket->misAssignee => TroubleTicket::IN_PROGRESS,
            default => TroubleTicket::PENDING,
        };

        $troubleTicket->setStatus($status);
        $troubleTicket->assignee = $this->manager->getDefaultAssignee($troubleTicket);
        $troubleTicket->closedAt = null;

        return $troubleTicket;
    }
}
