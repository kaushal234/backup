<?php

declare(strict_types=1);

namespace App\Security\Voter\MIS\TroubleTicket;

use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class TroubleTicketReopenVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_TROUBLE_TICKET_REOPEN_VOTER' === $attribute && $subject instanceof TroubleTicket;
    }

    /**
     * {@inheritdoc}
     *
     * @param TroubleTicket $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        if (null === $subject->closedAt) {
            return false;
        }

        return ($user === $subject->module->getKeyUser()
                || $user === $subject->module->getOperationalOwner()
                || $subject->module->getLocalKeyUsers()->contains($user)
                || $user === $subject->createdBy)
            && $subject->closedAt > (new \DateTime('1 month ago'))
        ;
    }
}
