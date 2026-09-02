<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\SalesForecast;

use App\Entity\Directory\People;
use App\Entity\Sales\SalesForecastFile;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class SalesForecastFileVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}.
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'SALES_FORECAST_EDIT_VOTER' === $attribute && $subject instanceof SalesForecastFile;
    }

    /**
     * {@inheritdoc}.
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof People) {
            return false;
        }

        return $this->getSecurity()->isGranted('SALES_FORECAST_EDIT_VOTER', $subject->getSalesForecast());
    }
}
