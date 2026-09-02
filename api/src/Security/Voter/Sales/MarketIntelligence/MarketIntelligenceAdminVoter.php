<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\MarketIntelligence;

use App\Entity\Directory\People;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class MarketIntelligenceAdminVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'MARKET_INTELLIGENCE_ADMIN_VOTER' === $attribute && $subject instanceof MarketIntelligence;
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

        return $this->getSecurity()->isGranted('FEATURE_MARKET_INTELLIGENCE_ADMIN')
            || $user === $subject->getPoster()
            || $this->getSecurity()->isGranted('MOO_MIM')
        ;
    }
}
