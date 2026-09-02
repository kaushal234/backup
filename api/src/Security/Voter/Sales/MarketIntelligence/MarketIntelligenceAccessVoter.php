<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\MarketIntelligence;

use App\Entity\Directory\People;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class MarketIntelligenceAccessVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'MARKET_INTELLIGENCE_VIEW_VOTER' === $attribute && $subject instanceof MarketIntelligence;
    }

    /**
     * {@inheritdoc}
     *
     * @param MarketIntelligence $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        if ($user === $subject->getPoster()) {
            return true;
        }

        if ($subject->getPositionLevels()->isEmpty()) {
            return true;
        }

        if (null !== $user->getPosition() && $subject->getPositionLevels()->contains($user->getPosition()->getLevel())) {
            return true;
        }

        return (bool) $this->getSecurity()->isGranted('MOO_MIM');
    }
}
