<?php

declare(strict_types=1);

namespace App\Security\Voter\Specification;

use App\Entity\Directory\People;
use App\Entity\Module\Specification\Specification;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class SpecificationWriteVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_SPECIFICATION_WRITE_VOTER' === $attribute && $subject instanceof Specification;
    }

    /**
     * @param Specification $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        $module = $subject->module;

        return $this->getSecurity()->isGranted('FEATURE_SPECIFICATION_CREATE')
            || $module->getOperationalOwner() === $user
            || $module->getKeyUser() === $user
            || $module->getLocalKeyUsers()->contains($user);
    }
}
