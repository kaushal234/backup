<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\Customer;

use App\Entity\Directory\People;
use App\Entity\Sales\AbstractSalesRepresentative;
use App\Entity\Sales\Customer;
use App\Manager\Directory\PeopleManager;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class CustomerFileVoter extends AbstractVoter
{
    /**
     * @var string
     */
    final public const MODULE_NAME = 'ECUST';

    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[EntityManagerInterface::class]];
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Customer
            && \in_array($attribute, ['CUSTOMER_FILES_READ_VOTER', 'CUSTOMER_FILES_DELETE_VOTER', 'CUSTOMER_FILES_UPLOAD_VOTER'], true);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof People) {
            return false;
        }

        switch ($attribute) {
            case 'CUSTOMER_FILES_READ_VOTER':
                return $this->hasReadPermission($subject, $user);
            case 'CUSTOMER_FILES_DELETE_VOTER':
                return $this->hasDeletePermission($user);
            case 'CUSTOMER_FILES_UPLOAD_VOTER':
                return $this->hasUploadPermission($subject, $user);
        }

        return false;
    }

    private function hasReadPermission(Customer $customer, People $user): bool
    {
        return $this->hasPermission($customer, $user, 'FEATURE_CUSTOMER_FILES_READ', ['ROLE_CEO', 'ROLE_CFO', 'ROLE_EVP']);
    }

    private function hasDeletePermission(People $user): bool
    {
        return $this->getSecurity()->isGranted('FEATURE_CUSTOMER_FILES_DELETE') || $this->getSecurity()->isGranted('MOO_ECUST');
    }

    private function hasUploadPermission(Customer $customer, People $user): bool
    {
        return $this->hasPermission($customer, $user, 'FEATURE_CUSTOMER_FILES_UPLOAD', ['ROLE_RCEO', 'ROLE_SAM']);
    }

    private function hasPermission(Customer $customer, People $user, string $feature, array $groups): bool
    {
        $security = $this->getSecurity();
        if ($security->isGranted($feature)) {
            return true;
        }

        /** @var AbstractSalesRepresentative|null $salesRepresentative */
        foreach ([$customer->getMainSalesRepresentative(), ...$customer->getSecondarySalesRepresentatives()] as $salesRepresentative) {
            $asm = $salesRepresentative->asm ?? null;
            $supervisor = null !== $asm ? $asm->getSupervisor() : null;

            if ((null !== $asm && $asm->getId() === $user->getId()) || (null !== $supervisor && $supervisor->getId() === $user->getId())) {
                return true;
            }

            if (null !== $asm && null !== $asm->getBusinessUnit() && PeopleManager::hasOneOfGroups($user, $groups, $asm->getBusinessUnit()->getLocation())) {
                return true;
            }
        }

        return false;
    }
}
