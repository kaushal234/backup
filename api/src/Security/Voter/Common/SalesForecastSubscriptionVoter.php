<?php

declare(strict_types=1);

namespace App\Security\Voter\Common;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\Sales\SalesForecast;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class SalesForecastSubscriptionVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[IriConverterInterface::class]];
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return \in_array($attribute, ['SUBSCRIPTION_DELETE_VOTER', 'SUBSCRIPTION_CREATE_VOTER'], true) && $subject instanceof Subscription && 0 === mb_strpos($subject->getResource(), '/sales/sales_forecasts');
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!$subject->getUser() instanceof People) {
            return false;
        }

        try {
            $item = $this->serviceLocator->get(IriConverterInterface::class)->getResourceFromIri($subject->getResource());
        } catch (\Exception $exception) {
            return false;
        }

        if (!$item instanceof SalesForecast) {
            return false;
        }

        $security = $this->getSecurity();
        if ($security->isGranted('MOO_SFR')) {
            return true;
        }

        return (bool) $security->isGranted('SALES_FORECAST_EDIT_VOTER', $item);
    }
}
