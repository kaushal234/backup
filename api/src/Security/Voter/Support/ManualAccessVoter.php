<?php

declare(strict_types=1);

namespace App\Security\Voter\Support;

use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Support\Manual;
use App\Repository\Sales\ExtranetUserAclRepository;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ManualAccessVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [ExtranetUserAclRepository::class];
    }

    protected function supports(string $attribute, $subject): bool
    {
        return 'MANUAL_ACCESS_VOTER' === $attribute && $subject instanceof Manual;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        /** @var EquipmentRecord $equipmentRecord */
        $equipmentRecord = $subject->equipmentRecord;

        // Access all manuals for intranet users.
        if ($user instanceof People) {
            return true;
        }

        // Denied access to manual not linked to ER for not intranet users.
        if (null === $equipmentRecord) {
            return false;
        }

        if (!$user instanceof ExtranetUser) {
            return false;
        }

        /** @var ExtranetUserAclRepository $repository */
        $repository = $this->serviceLocator->get(ExtranetUserAclRepository::class);

        return (null !== $equipmentRecord->getEndUser() && (bool) $repository->findCustomerRelationTeamForCustomer($user, $equipmentRecord->getEndUser()))
            || (null !== $equipmentRecord->getMaintainer() && (bool) $repository->findCustomerRelationTeamForCustomer($user, $equipmentRecord->getMaintainer()))
            || (null !== $equipmentRecord->getBuyer() && (bool) $repository->findCustomerRelationTeamForCustomer($user, $equipmentRecord->getBuyer()))
        ;
    }
}
