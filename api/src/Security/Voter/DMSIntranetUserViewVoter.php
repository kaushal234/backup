<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Directory\People;
use App\Entity\DMS;
use App\Entity\DMSRestriction;
use App\Manager\Directory\PeopleManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class DMSIntranetUserViewVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof DMS && 'DMS_PEOPLE_VIEW_VOTER' === $attribute;
    }

    /**
     * {@inheritdoc}
     *
     * @param DMS $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        if ($subject->getOwner() === $user) {
            return true;
        }

        if ($this->getSecurity()->isGranted('FEATURE_DMS_VIEW_ALL')) {
            return true;
        }

        // Legacy logic -> GRANT if user has ROLE_QAM on the owner BU (Task#452440)
        if ($subject->isConfidential() && PeopleManager::hasGroup($user, 'ROLE_QAM', $subject->getOwner()->getBusinessUnit()->getLocation())) {
            return true;
        }

        foreach ($subject->getRestrictions() as $restriction) {
            if ($this->isFromBusinessUnitOrRegionOrSubDivisionOrDivision($user, $restriction) && $this->hasPositionOrIsFromDepartment($user, $restriction)) {
                return true;
            }
        }

        return !$subject->isConfidential();
    }

    private function isFromBusinessUnitOrRegionOrSubDivisionOrDivision(People $people, DMSRestriction $restriction): bool
    {
        if (null === $restriction->businessUnit
            && null === $restriction->region
            && null === $restriction->subDivision
            && null === $restriction->division
        ) {
            return true;
        }

        if (null !== $restriction->businessUnit) {
            return $restriction->businessUnit === $people->getBusinessUnit();
        }

        if (null !== $restriction->region) {
            return $restriction->region === $people->getBusinessUnit()->getRegion();
        }

        if (null !== $restriction->subDivision) {
            return $restriction->subDivision === $people->getBusinessUnit()->getRegion()->getSubDivision();
        }

        // Last case, division is not null here
        return $restriction->division === $people->getBusinessUnit()->getRegion()->getSubDivision()->division;
    }

    private function hasPositionOrIsFromDepartment(People $people, DMSRestriction $restriction): bool
    {
        if (null === $restriction->position && null === $restriction->department) {
            return true;
        }

        if (null !== $restriction->position) {
            return $restriction->position === $people->getPosition();
        }

        // Last case, department is not null here
        return $restriction->department === $people->getDepartment();
    }
}
