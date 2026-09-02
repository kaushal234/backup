<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Directory\People;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;

class PeopleAccessVoter extends RoleVoter
{
    public function __construct()
    {
        parent::__construct(UserAccessVoter::PREFIX);
    }

    protected function extractRoles(TokenInterface $token): array
    {
        $user = $token->getUser();

        if (
            $user instanceof People
            && null !== $user->getBusinessUnit()
            && null !== ($erp = $user->getBusinessUnit()->getLocation()->getErp())
        ) {
            return [UserAccessVoter::PREFIX.'PEOPLE_'.$erp];
        }

        return [];
    }
}
