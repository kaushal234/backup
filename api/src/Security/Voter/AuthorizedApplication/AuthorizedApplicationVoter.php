<?php

declare(strict_types=1);

namespace App\Security\Voter\AuthorizedApplication;

use App\Entity\AuthorizedApplication;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;

class AuthorizedApplicationVoter extends RoleVoter
{
    public function __construct()
    {
        parent::__construct('AUTHORIZED_APPLICATION_');
    }

    protected function extractRoles(TokenInterface $token): array
    {
        $user = $token->getUser();
        if (!$user instanceof AuthorizedApplication) {
            return [];
        }

        $features = [];
        foreach ($user->getFeatures() as $feature) {
            $features[] = \sprintf('AUTHORIZED_APPLICATION_%s', $feature->getName());
        }

        return $features;
    }
}
