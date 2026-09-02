<?php

declare(strict_types=1);

namespace App\Security\Voter\Service;

use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Repository\EquipmentRecordRepository;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class CustomerServiceRecordFileAccessVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[EntityManagerInterface::class]];
    }

    public function supports(string $attribute, $subject): bool
    {
        return 'CSR_ACCESS_VOTER' === $attribute && $subject instanceof AbstractCustomerServiceRecord;
    }

    public function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if ($user instanceof People) {
            return true;
        }

        if (!$user instanceof ExtranetUser) {
            return false;
        }

        $acls = $user->getExtranetUserAcls();

        /** @var EquipmentRecordRepository $erRepository */
        $erRepository = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(EquipmentRecord::class);

        foreach ($acls as $acl) {
            $crt = $acl->getCrt();
            $projectNumber = $subject->equipmentRecord->getProjectNumber();
            $equipment = $erRepository->getEquipmentByExtranetUserCrtCustomer($crt->getCustomer(), $projectNumber);
            if (null !== $equipment && $equipment->getSerialNumber() === $projectNumber) {
                return true;
            }
        }

        return false;
    }
}
