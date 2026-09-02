<?php

declare(strict_types=1);

namespace App\Security\Voter\MIS\Module;

use App\Entity\Directory\People;
use App\Entity\Module\Module;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ModuleWriteVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_MODULE_WRITE_VOTER' === $attribute && $subject instanceof Module;
    }

    /**
     * @param Module $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        return $this->getSecurity()->isGranted('FEATURE_MODULE_WRITE')
            || $subject->getOperationalOwner() === $user
            || $subject->getKeyUser() === $user;
    }
}
