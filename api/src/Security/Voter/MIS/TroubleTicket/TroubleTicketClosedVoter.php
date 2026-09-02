<?php

declare(strict_types=1);

namespace App\Security\Voter\MIS\TroubleTicket;

use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Repository\Common\SubscriptionRepository;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class TroubleTicketClosedVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), SubscriptionRepository::class];
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_TROUBLE_TICKET_CLOSED_VOTER' === $attribute && $subject instanceof TroubleTicket;
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

        return !\in_array($subject->getStatus(), TroubleTicket::CLOSED_STATUSES, true);
    }
}
