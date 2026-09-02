<?php

declare(strict_types=1);

namespace App\Controller\Sales\SalesForecast;

use App\Entity\Sales\SalesForecast;
use App\Notifier\Sales\SalesForecast\SalesForecastNotifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SalesForecastNotifyController extends AbstractController
{
    private readonly SalesForecastNotifier $notifier;

    public function __construct(SalesForecastNotifier $notifier)
    {
        $this->notifier = $notifier;
    }

    public function __invoke(SalesForecast $data)
    {
        if (!\in_array($data->getStatus(), [SalesForecast::ORDERED, SalesForecast::LOST, SalesForecast::PARTIAL, SalesForecast::ORDER_CANCELLED], true)) {
            return $data;
        }

        if (null !== $data->getClosureNotificationSentAt()) {
            return $data;
        }

        $clonedSalesForecast = clone $data;
        $clonedSalesForecast->setStatus(SalesForecast::IN_PROGRESS);

        $authorizationChecker = $this->container->get('security.authorization_checker');
        if (
            !$authorizationChecker->isGranted('SALES_FORECAST_ACCESS_VOTER', $clonedSalesForecast)
            || (
                !$authorizationChecker->isGranted('SALES_FORECAST_EDIT_VOTER', $clonedSalesForecast)
                && !$authorizationChecker->isGranted('MOO_SFR')
                && !$authorizationChecker->isGranted('FEATURE_SALES_FORECAST_FORCE_STATUS')
            )
        ) {
            throw $this->createAccessDeniedException('You are not allowed to trigger the notification on this SFR');
        }

        $data->setClosureNotificationSentAt(new \DateTime());
        $this->notifier->sendEmail($data, 'sfr.subject.closure', 'sales_forecast_closure.html.twig');

        return $data;
    }
}
