<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\Demo;

use App\Entity\Directory\People;
use App\Entity\Sales\Demo;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class DemoEditVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'DEMO_EDIT_VOTER' === $attribute && $subject instanceof Demo;
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

        $security = $this->getSecurity();
        if ($security->isGranted('DEMO_ADMIN_EDIT_VOTER', $subject)) {
            return true;
        }

        if ($security->isGranted('MOO_DEMO')) {
            return true;
        }

        if (\in_array($subject->getStatus(), Demo::CLOSED_STATUSES, true)) {
            return false;
        }

        return (bool) $security->isGranted('FEATURE_DEMO_EDIT');
    }
}
