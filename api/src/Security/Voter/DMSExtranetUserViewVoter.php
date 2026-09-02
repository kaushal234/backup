<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\DMS;
use App\Entity\Sales\ExtranetUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class DMSExtranetUserViewVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof DMS && 'EXTRANET_DMS_VIEW_VOTER' === $attribute;
    }

    /**
     * {@inheritdoc}
     *
     * @param DMS $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof ExtranetUser) {
            return false;
        }

        return $this->getSecurity()->isGranted('ACCESS_EXTRANET_USER')
            && !$subject->isConfidential()
            && 'EXTRANET' === $subject->getPortal()
        ;
    }
}
