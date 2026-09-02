<?php

declare(strict_types=1);

namespace App\Security\Voter\Support;

use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserAcl;
use App\Entity\Support\EquipmentAccident;
use App\Entity\Support\EquipmentFollowUpReport;
use App\Entity\Support\EquipmentHourmeterReset;
use App\Entity\Support\EquipmentMaintenance;
use App\Entity\Support\MaintenanceContract;
use App\Repository\Sales\ExtranetUserAclRepository;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ExtranetUserEndUserVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [EntityManagerInterface::class];
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'EQUIPMENT_END_USER_VOTER' === $attribute && \in_array($subject::class, [EquipmentAccident::class, EquipmentMaintenance::class, EquipmentRecord::class, EquipmentFollowUpReport::class, EquipmentHourmeterReset::class], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if ($user instanceof People) {
            return true;
        }
        if (!$user instanceof ExtranetUser) {
            return false;
        }

        switch (true) {
            case $subject instanceof EquipmentAccident:
                $equipmentRecord = $subject->getFollowUpReport()->getEquipmentRecord();
                break;
            case $subject instanceof EquipmentMaintenance:
            case $subject instanceof EquipmentFollowUpReport:
            case $subject instanceof EquipmentHourmeterReset:
                $equipmentRecord = $subject->getEquipmentRecord();
                break;
            case $subject instanceof EquipmentRecord:
                $equipmentRecord = $subject;
                break;
            default:
                return false;
        }

        $activesContracts = $equipmentRecord->getContracts()->filter(static fn (MaintenanceContract $contract) => $contract->isActive());

        // If the unit is under active contract the end user must be linked to the contract directly
        if (!$activesContracts->isEmpty()) {
            $contracts = $activesContracts->filter(static fn (MaintenanceContract $contract) => $contract->getEndUserRepresentatives()->contains($user));

            return !$contracts->isEmpty();
        }

        /** @var ExtranetUserAclRepository $repo */
        $repo = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(ExtranetUserAcl::class);

        if (null === $equipmentRecord->getEndUser()) {
            return false;
        }

        return (bool) $repo->findCustomerRelationTeamForCustomer($user, $equipmentRecord->getEndUser());
    }
}
