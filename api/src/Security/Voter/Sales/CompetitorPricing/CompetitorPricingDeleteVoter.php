<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\CompetitorPricing;

use App\Entity\Directory\People;
use App\Entity\Sales\CompetitorPricing;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class CompetitorPricingDeleteVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'COMPETITOR_PRICING_DELETE_VOTER' === $attribute && $subject instanceof CompetitorPricing;
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

        if ($user === $subject->getPoster()) {
            return true;
        }

        if (
            null !== ($supervisor = $subject->getPoster()->getSupervisor())
            && $supervisor === $user
        ) {
            return true;
        }

        return null !== $subject->getForecastClosure()
        && $this->getSecurity()->isGranted('FEATURE_SALES_FORECAST_ADMIN_EDIT', $subject->getForecastClosure()->getSalesForecast());
    }
}
