<?php

declare(strict_types=1);

namespace App\Security\Voter\Support;

use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Support\Manual;
use App\Manager\Directory\PeopleManager;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

/**
 * Only MOO of PUBS can edit a manual with no equipment record.
 */
class ManualEditVoter extends AbstractVoter
{
    protected function supports(string $attribute, $subject): bool
    {
        return 'MANUAL_EDIT_VOTER' === $attribute;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        $security = $this->getSecurity();

        if (!$user instanceof People) {
            return false;
        }

        if ($security->isGranted('MOO_PUBS') || PeopleManager::hasGroup($user, 'SUPERUSER')) {
            return true;
        }

        /** @var EquipmentRecord $subject */
        if (null === $subject) {
            return false;
        }

        return $security->isGranted('FEATURE_MANUAL_ADMIN');
    }
}
