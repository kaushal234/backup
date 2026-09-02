<?php

declare(strict_types=1);

namespace App\Manager\MIS\TroubleTicket;

use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\MIS\TroubleTicket\Type;
use App\Entity\MIS\TroubleTicket\TypeDefaultAssignee;
use App\Manager\Directory\PeopleManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RequestStack;

class TroubleTicketManager
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getNewStatus(): ?string
    {
        /** @var TroubleTicket $troubleTicket */
        $troubleTicket = $this->requestStack->getCurrentRequest()->attributes->get('data');

        /** @var TroubleTicket $previousData */
        $previousData = $this->requestStack->getCurrentRequest()->attributes->get('previous_data');

        /** @var People $user */
        $user = $this->security->getUser();

        if (null === $user) {
            throw new BadRequestException();
        }

        if (null !== $troubleTicket->assignee
            && $previousData->assignee !== $troubleTicket->assignee
            && \in_array($troubleTicket->getStatus(), [TroubleTicket::IN_PROGRESS, TroubleTicket::PENDING, TroubleTicket::PENDING_MOO], true)) {
            if (\in_array($troubleTicket->getStatus(), [TroubleTicket::IN_PROGRESS, TroubleTicket::PENDING], true)) {
                return TroubleTicket::AWAITING_USER;
            }

            if (Type::INCIDENT === $troubleTicket->type->type && !$troubleTicket->module->isMisRelative()) {
                return TroubleTicket::AWAITING_USER_MOO;
            }

            return Type::INCIDENT === $troubleTicket->type->type ? TroubleTicket::AWAITING_USER : TroubleTicket::AWAITING_USER_MOO;
        }

        if (TroubleTicket::AWAITING_USER === $troubleTicket->getStatus() && null === $troubleTicket->assignee) {
            return null === $troubleTicket->misAssignee ? TroubleTicket::PENDING : TroubleTicket::IN_PROGRESS;
        }

        if (TroubleTicket::SOLUTION_PROPOSED === $troubleTicket->getStatus() && null === $troubleTicket->assignee) {
            return TroubleTicket::IN_PROGRESS;
        }

        if (TroubleTicket::PENDING_MOO === $troubleTicket->getStatus()
            && (
                (PeopleManager::hasGroup($user, 'GG_MIS') && \in_array($previousData->assignee, [null, $troubleTicket->module->getOperationalOwner(), $troubleTicket->module->getKeyUser()], true))
                || \in_array($previousData->assignee, [$troubleTicket->module->getOperationalOwner(), $troubleTicket->module->getKeyUser()], true)
                || $troubleTicket->module->getLocalKeyUsers()->contains($previousData->assignee)
            )
            && null === $troubleTicket->assignee
        ) {
            return TroubleTicket::PENDING;
        }

        if (\in_array($troubleTicket->getStatus(), [TroubleTicket::AWAITING_USER_MOO, TroubleTicket::SOLUTION_PROPOSED_MOO], true)
            && $previousData->assignee !== $troubleTicket->assignee
            && (\in_array($troubleTicket->assignee, [$troubleTicket->module->getOperationalOwner(), $troubleTicket->module->getKeyUser()], true)
                || $troubleTicket->module->getLocalKeyUsers()->contains($previousData->assignee))
        ) {
            return TroubleTicket::PENDING_MOO;
        }

        return null;
    }

    public function getDefaultAssignee(TroubleTicket $troubleTicket): ?People
    {
        /** @var EntityRepository $typeDefaultAssigneeRepository */
        $typeDefaultAssigneeRepository = $this->entityManager->getRepository(TypeDefaultAssignee::class);
        /** @var TypeDefaultAssignee|null $typeDefaultAssignee */
        $typeDefaultAssignee = $typeDefaultAssigneeRepository->findOneBy(['type' => $troubleTicket->type, 'module' => $troubleTicket->module]);

        if (null !== $typeDefaultAssignee) {
            if (TypeDefaultAssignee::OPERATIONAL_OWNER === $typeDefaultAssignee->defaultAssignee) {
                return $troubleTicket->module->getOperationalOwner();
            }

            foreach ($troubleTicket->module->getLocalKeyUsers() as $localKeyUser) {
                if ($localKeyUser->getBusinessUnit()->getRegion() === $troubleTicket->createdBy->getBusinessUnit()->getRegion()) {
                    return $localKeyUser;
                }
            }

            if (null === $troubleTicket->module->getKeyUser()) {
                return $troubleTicket->module->getOperationalOwner();
            }

            return $troubleTicket->module->getKeyUser();
        }

        return null;
    }
}
