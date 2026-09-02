<?php

declare(strict_types=1);

namespace App\Security\Voter\Finance;

use App\Entity\Directory\People;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class FinanceFamilyCreationVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[LocationRepository::class, PeopleRepository::class]];
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'FINANCE_FAMILY_CREATE_VOTER' === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }
        $tldGroupLocation = $this->serviceLocator->get(LocationRepository::class)->findOneBy(['name' => 'TLD GRP']);

        $groupControllers = [];
        if (null !== $tldGroupLocation) {
            $groupControllers = $this->serviceLocator->get(PeopleRepository::class)->findGroupsMembers(['ROLE_FC'], $tldGroupLocation);
        }

        return $this->getSecurity()->isGranted('FEATURE_FINANCE_FAMILY_CREATE') || \in_array($user, $groupControllers, true);
    }
}
