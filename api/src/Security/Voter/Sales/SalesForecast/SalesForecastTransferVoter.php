<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\SalesForecast;

use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class SalesForecastTransferVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}.
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'FEATURE_SALES_FORECAST_TRANSFER' === $attribute;
    }

    /**
     * {@inheritdoc}.
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return $this->getSecurity()->isGranted('MOO_SFR');
    }
}
