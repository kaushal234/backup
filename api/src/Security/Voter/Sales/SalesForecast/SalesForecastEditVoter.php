<?php

declare(strict_types=1);

namespace App\Security\Voter\Sales\SalesForecast;

use App\Entity\Directory\People;
use App\Entity\Sales\SalesForecast;
use App\Manager\Directory\PeopleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class SalesForecastEditVoter extends AbstractSalesForecastVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[EntityManagerInterface::class]];
    }

    /**
     * {@inheritdoc}.
     */
    protected function supports(string $attribute, $subject): bool
    {
        return
            \in_array($attribute, ['SALES_FORECAST_EDIT_VOTER', 'FORECAST_CLOSURE_WRITE_VOTER'], true)
            && $subject instanceof SalesForecast;
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

        if (!$subject instanceof SalesForecast) {
            return false;
        }

        $security = $this->getSecurity();
        if ($security->isGranted('MOO_SFR')) {
            return true;
        }

        if ('SALES_FORECAST_EDIT_VOTER' === $attribute && $security->isGranted('FEATURE_SALES_FORECAST_FORCE_STATUS')) {
            return true;
        }

        if ('SALES_FORECAST_EDIT_VOTER' === $attribute && !\in_array($subject->getStatus(), SalesForecast::OPEN_STATUSES, true)) {
            return false;
        }

        $uow = $this->serviceLocator->get(EntityManagerInterface::class)->getUnitOfWork();
        $uow->computeChangeSets();
        $changeSet = $uow->getEntityChangeSet($subject);

        if (isset($changeSet['status']) && \in_array($changeSet['status'][0], [SalesForecast::CANCELLED, SalesForecast::LOST, SalesForecast::ORDERED, SalesForecast::PARTIAL, SalesForecast::ORDER_CANCELLED], true)) {
            return false;
        }

        return
            $security->isGranted('SALES_FORECAST_ACCESS_VOTER', $subject)
            && (
                $security->isGranted('FEATURE_SALES_FORECAST_ADMIN_EDIT')
                || $security->isGranted('FEATURE_SALES_FORECAST_RESTRICTED_EDIT')
                || $security->isGranted('FEATURE_SALES_FORECAST_FACTORY_EDIT')
                || (null !== $subject->getBuyer() && PeopleManager::isAsmOfCustomerOrParent($user, $subject->getBuyer()))
                || (null !== $subject->getEndUser() && PeopleManager::isAsmOfCustomerOrParent($user, $subject->getEndUser()))
            )
        ;
    }
}
