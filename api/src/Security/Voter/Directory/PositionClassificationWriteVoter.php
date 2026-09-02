<?php

declare(strict_types=1);

namespace App\Security\Voter\Directory;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\People;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class PositionClassificationWriteVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'POSITION_CLASSIFICATION_WRITE_VOTER' === $attribute && $subject instanceof BusinessUnit;
    }

    /**
     * @param BusinessUnit $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        return
            $this->getSecurity()->isGranted('FEATURE_POSITION_CLASSIFICATION_WRITE_FULL')
            || $this->getSecurity()->isGranted('FEATURE_POSITION_CLASSIFICATION_WRITE_BU_'.$subject->getLocation()->getId())
            || $this->getSecurity()->isGranted('MOO_ESM')
        ;
    }
}
