<?php

declare(strict_types=1);

namespace App\Security\Voter\Directory;

use App\Entity\Directory\People;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;

class SubDivisionVoter extends RoleVoter
{
    public function __construct()
    {
        parent::__construct('SUBDIVISION_');
    }

    protected function extractRoles(TokenInterface $token): array
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return [];
        }

        if (null === $businessUnit = $user->getBusinessUnit()) {
            return [];
        }

        if (null === $region = $businessUnit->getRegion()) {
            return [];
        }

        if (null === $subDivision = $region->getSubDivision()) {
            return [];
        }

        $features = [];
        foreach ($subDivision->getFeatures() as $feature) {
            $features[] = \sprintf('SUBDIVISION_%s', $feature->getName());
        }

        return $features;
    }
}
