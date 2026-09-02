<?php

declare(strict_types=1);

namespace AppBundle\Twig\Extension;

use ApiBundle\Model\ApiData;
use ApiBundle\Model\User;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TroubleTicketMenuContextExtension extends AbstractExtension
{
    private const CLOSED_STATUSES = ['SOLVED', 'NOT AN ISSUE', 'ALREADY RAISED', 'NOT APPROVED'];
    private const MIS_SIDE_STATUSES = ['PENDING', 'IN PROGRESS'];
    private const USER_SIDE_STATUSES = ['SOLUTION PROPOSED', 'AWAITING USER', 'MOO/GKU AWAITING USER'];

    public function __construct(private readonly Security $security)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('trouble_ticket_menu_context', [$this, 'getContext']),
        ];
    }

    /**
     * @param ApiData|array<string, mixed> $troubleTicket
     *
     * @return array<string, bool>
     */
    public function getContext(ApiData|array $troubleTicket): array
    {
        $user = $this->security->getUser();
        $userIri = $user instanceof User ? $user->iriId : null;

        $module = $troubleTicket['module'] ?? [];
        $localKeyUsers = $module['localKeyUsers'] ?? [];
        $operationalOwnerIri = $module['operationalOwner']['@id'] ?? null;
        $keyUserIri = $module['keyUser']['@id'] ?? null;
        $assigneeIri = $troubleTicket['assignee']['@id'] ?? null;
        $createdByIri = $troubleTicket['createdBy']['@id'] ?? null;
        $misAssigneeIri = $troubleTicket['misAssignee']['@id'] ?? null;
        $status = $troubleTicket['status'] ?? null;
        $typeType = $troubleTicket['type']['type'] ?? null;
        $ticketIri = $troubleTicket['@id'] ?? null;

        $isLocalKeyUser = false;
        $assigneeIsNotLocalKeyUser = true;
        foreach ($localKeyUsers as $localKeyUser) {
            $localKeyUserIri = $localKeyUser['@id'] ?? null;
            if (null !== $userIri && $userIri === $localKeyUserIri) {
                $isLocalKeyUser = true;
            }
            if (null !== $assigneeIri && $assigneeIri === $localKeyUserIri) {
                $assigneeIsNotLocalKeyUser = false;
            }
        }

        return [
            'isLocalKeyUser' => $isLocalKeyUser,
            'assigneeIsNotLocalKeyUser' => $assigneeIsNotLocalKeyUser,
            'troubleTicketIsOpen' => !\in_array($status, self::CLOSED_STATUSES, true),
            'troubleTicketIsOnMisSide' => \in_array($status, self::MIS_SIDE_STATUSES, true),
            'troubleTicketIsOnUserSide' => \in_array($status, self::USER_SIDE_STATUSES, true),
            'isMoo' => null !== $operationalOwnerIri && $userIri === $operationalOwnerIri,
            'isKeyUser' => null !== $keyUserIri && $userIri === $keyUserIri,
            'isAssignee' => null !== $assigneeIri && $assigneeIri === $userIri,
            'isAssignor' => null !== $createdByIri && $createdByIri === $userIri,
            'canTransfer' => null !== $ticketIri && $this->security->isGranted('FEATURE_TROUBLE_TICKET_TRANSFER_VOTER', $ticketIri),
            'assigneeIsNotTheMOO' => null === $assigneeIri || (null !== $operationalOwnerIri && $assigneeIri !== $operationalOwnerIri),
            'assigneeIsNotKeyUser' => null === $assigneeIri || null === $keyUserIri || $assigneeIri !== $keyUserIri,
            'isMIS' => $this->security->isGranted('ACL_GG_MIS'),
            'isIncident' => 'Incident' === $typeType,
            'isRequest' => 'Request' === $typeType,
            'isNotMISAssignee' => null !== $misAssigneeIri && $misAssigneeIri !== $userIri,
        ];
    }
}
