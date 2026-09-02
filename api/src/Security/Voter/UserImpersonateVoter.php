<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\Entity\Sales\ExtranetUser;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class UserImpersonateVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof User && 'USER_IMPERSONATE_VOTER' === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $security = $this->getSecurity();
        if (null === ($user = $security->getUser()) || !$user instanceof People) {
            return false;
        }

        foreach (['FEATURE_USER_IMPERSONATE' => People::class, 'FEATURE_EXTRANET_USER_IMPERSONATE' => ExtranetUser::class, 'FEATURE_VENDOR_USER_IMPERSONATE' => VendorUser::class] as $feature => $class) {
            if ($subject instanceof $class && $security->isGranted($feature)) {
                return true;
            }
        }

        return false;
    }
}
