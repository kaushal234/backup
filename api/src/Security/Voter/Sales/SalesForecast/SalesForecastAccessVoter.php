<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\SalesForecast;

use App\Entity\Directory\People;
use App\Entity\Sales\CustomerType;
use App\Entity\Sales\SalesArea;
use App\Entity\Sales\SalesForecast;
use App\Manager\Directory\PeopleManager;
use App\Repository\Common\SubscriptionRepository;
use App\Repository\Sales\SalesForecastRepository;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class SalesForecastAccessVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[SalesForecastRepository::class, SubscriptionRepository::class]];
    }

    /**
     * {@inheritdoc}.
     */
    protected function supports(string $attribute, $subject): bool
    {
        return 'SALES_FORECAST_ACCESS_VOTER' === $attribute && $subject instanceof SalesForecast;
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

        // current user is follower of the SFR
        if ($this->serviceLocator->get(SubscriptionRepository::class)->isFollowingResource($user, $subject)) {
            return true;
        }

        $security = $this->getSecurity();
        // current user has full access
        if ($security->isGranted('FEATURE_SALES_FORECAST_VIEW_FULL')) {
            return true;
        }

        // current user is the MOO
        if ($security->isGranted('MOO_SFR')) {
            return true;
        }

        $viewASM = $security->isGranted('FEATURE_SALES_FORECAST_VIEW_ASM');

        // current user is the ASM in charge of the SFR or current user is the supervisor (or the supervisor of the supervisor) of the ASM in charge of the SFR
        if (
            $viewASM
            && (
                $subject->getAsm()->getId() === $user->getId()
                || (null !== ($supervisor = $subject->getAsm()->getSupervisor()) && $supervisor->getId() === $user->getId())
                || (null !== $supervisor && null !== ($superSupervisor = $supervisor->getSupervisor()) && $superSupervisor->getId() === $user->getId())
            )
        ) {
            return true;
        }

        // current user is an ASM in charge of the SFR country
        if (
            $viewASM
            && null !== ($country = $subject->getCountry())
            && !$country->getSalesAreas()->filter(static fn (SalesArea $salesArea) => $salesArea->getAsm() === $user)->isEmpty()
        ) {
            return true;
        }

        // current user is the ASM of the customer (buyer or end user)
        if (
            ($security->isGranted('FEATURE_SALES_FORECAST_VIEW_CUSTOMER') || $viewASM)
            && (
                (null !== $subject->getBuyer() && PeopleManager::isAsmOfCustomerOrParent($user, $subject->getBuyer()))
                || (null !== $subject->getEndUser() && PeopleManager::isAsmOfCustomerOrParent($user, $subject->getEndUser()))
            )
        ) {
            return true;
        }

        // current user is granted the permission on the right SSO
        if ($security->isGranted('FEATURE_SALES_FORECAST_VIEW_SSO_'.$subject->getSso()->getId())) {
            return true;
        }

        $factoryId = $this->serviceLocator->get(SalesForecastRepository::class)->getOriginalFactoryId($subject);

        // current user is granted the permission on the right Factory
        if ($security->isGranted('FEATURE_SALES_FORECAST_VIEW_FACTORY_'.$factoryId)) {
            return true;
        }

        // current user is VPM and the customer (buyer or end user) is a Military one
        return $security->isGranted('FEATURE_SALES_FORECAST_VIEW_MILITARY')
        && (
            (null !== $subject->getBuyer() && $subject->getBuyer()->getCustomerTypes()->filter(static fn (CustomerType $customerType) => CustomerType::MILITARY_TYPE_NAME === $customerType->getName())->count() > 0)
            || (null !== $subject->getEndUser() && $subject->getEndUser()->getCustomerTypes()->filter(static fn (CustomerType $customerType) => CustomerType::MILITARY_TYPE_NAME === $customerType->getName())->count() > 0)
        );
    }
}
