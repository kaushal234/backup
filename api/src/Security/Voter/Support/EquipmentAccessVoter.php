<?php

declare(strict_types=1);

namespace App\Security\Voter\Support;

use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\ExtranetUser;
use App\Repository\Sales\ExtranetUserAclRepository;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class EquipmentAccessVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [ExtranetUserAclRepository::class];
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'EQUIPMENT_ACCESS_VOTER' === $attribute && $subject instanceof EquipmentRecord;
    }

    /**
     * @param EquipmentRecord $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if ($user instanceof People) {
            return true;
        }
        if (!$user instanceof ExtranetUser) {
            return false;
        }

        $repository = $this->serviceLocator->get(ExtranetUserAclRepository::class);

        return (null !== $subject->getEndUser() && (bool) $repository->findCustomerRelationTeamForCustomer($user, $subject->getEndUser()))
            || (null !== $subject->getMaintainer() && (bool) $repository->findCustomerRelationTeamForCustomer($user, $subject->getMaintainer()))
            || (null !== $subject->getBuyer() && (bool) $repository->findCustomerRelationTeamForCustomer($user, $subject->getBuyer()))
        ;
    }
}
