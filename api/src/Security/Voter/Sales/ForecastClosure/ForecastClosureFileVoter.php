<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\ForecastClosure;

use App\Entity\Directory\People;
use App\Entity\Sales\ForecastClosureFile;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class ForecastClosureFileVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'FORECAST_CLOSURE_WRITE_VOTER' === $attribute && $subject instanceof ForecastClosureFile;
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

        return $this->getSecurity()->isGranted('FORECAST_CLOSURE_WRITE_VOTER', $subject->getForecastClosure()->getSalesForecast());
    }
}
