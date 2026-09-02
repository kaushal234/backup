<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\Demo;

use App\Entity\Directory\People;
use App\Entity\Sales\Demo;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class DemoStatusVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'DEMO_STATUS_VOTER' === $attribute && $subject instanceof Demo;
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

        if ($subject->getAsm() === $user) {
            return true;
        }

        $security = $this->getSecurity();
        if ($security->isGranted('FEATURE_DEMO_STATUS')) {
            return true;
        }

        if ($security->isGranted('FEATURE_DEMO_ADMIN')) {
            return true;
        }

        return $security->isGranted('MOO_DEMO');
    }
}
