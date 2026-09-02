<?php

declare(strict_types=1);

namespace App\Security\Voter\Directory\People;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PeopleRoleOnLocationVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Location;
    }

    /**
     * @param Location $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof People) {
            return false;
        }

        foreach ($user->getAcls() as $acl) {
            if (null === ($location = $acl->getLocation())) {
                continue;
            }

            if ($acl->getGroup()->getName() === $attribute && $location->getId() === $subject->getId()) {
                return true;
            }
        }

        return false;
    }
}
