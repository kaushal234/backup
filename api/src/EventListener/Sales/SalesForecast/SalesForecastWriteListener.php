<?php

declare(strict_types=1);

namespace App\EventListener\Sales\SalesForecast;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Dto\DeeplTranslator;
use App\Entity\Sales\MasterSalesForecast;
use App\Entity\Sales\SalesForecast;
use App\Factory\EmailChangeSetFactory;
use App\Http\DeeplClient;
use App\Manager\Manufacturing\IntelligentBatterySystemManager;
use App\Notifier\Sales\SalesForecast\SalesForecastNotifier;
use App\Request\Activity\CommentRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class SalesForecastWriteListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onSalesForecastEdition', EventPriorities::PRE_WRITE],
                ['afterSalesForecastEdition', EventPriorities::POST_WRITE],
                ['onSalesForecastSynchronizedEdition', EventPriorities::PRE_WRITE],
                ['afterSalesForecastCreation', EventPriorities::POST_WRITE],
                ['beforeSalesForecastDeletion', EventPriorities::PRE_WRITE],
                ['afterSalesForecastDeletion', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            CommentRequestManager::class,
            SalesForecastNotifier::class,
            EntityManagerInterface::class,
            PropertyAccessorInterface::class,
            IntelligentBatterySystemManager::class,
            DeeplClient::class,
            EmailChangeSetFactory::class,
        ];
    }

    public function onSalesForecastEdition(ViewEvent $event): void
    {
        $salesForecast = $event->getControllerResult();

        if (!$salesForecast instanceof SalesForecast || !$event->getRequest()->isMethod(Request::METHOD_PUT)) {
            return;
        }

        // if synchronization was required
        // and the SFR is not the only one in the MSFR
        // the other method must handle it
        if (
            $salesForecast->isSynchronized()
            && null !== $salesForecast->getMasterSalesForecast()
            && $salesForecast->getMasterSalesForecast()->getSalesForecasts()->count() > 1
        ) {
            return;
        }

        $changeSet = $this->serviceLocator->get(EmailChangeSetFactory::class)->createChangeSetForEmail($salesForecast, ['masterSalesForecast', 'lastComment', 'delinquent', 'lastCommentedAt', 'closureNotificationSentAt']);

        $previousSalesForecast = $event->getRequest()->attributes->get('previous_data');
        if (!isset($changeSet['status']) && $previousSalesForecast instanceof SalesForecast && $previousSalesForecast->getStatus() !== $salesForecast->getStatus()) {
            $changeSet['status'] = [$previousSalesForecast->getStatus(), $salesForecast->getStatus()];
        }

        // if the SFR is reopened, the FCR should all be deleted
        if (isset($changeSet['status']) && \in_array($changeSet['status'][0], [SalesForecast::PARTIAL, SalesForecast::LOST, SalesForecast::ORDERED, SalesForecast::ORDER_CANCELLED], true)) {
            $this->purgeForecastClosures($salesForecast, true);
            $salesForecast->setClosedAt(null);
        }

        // if the SFR is being closed, the notification will be sent when the last FCR is posted
        if (isset($changeSet['status']) && \in_array($changeSet['status'][1], [SalesForecast::PARTIAL, SalesForecast::LOST, SalesForecast::ORDERED], true)) {
            return;
        }

        if (isset($changeSet['status']) && SalesForecast::CANCELLED === $salesForecast->getStatus()) {
            $this->serviceLocator->get(SalesForecastNotifier::class)->sendEmail($salesForecast, 'sfr.subject.cancellation', 'sales_forecast_cancel.html.twig');

            return;
        }

        if (null !== $salesForecast->getComment()) {
            $translation = $this->serviceLocator->get(DeeplClient::class)->getTranslation($salesForecast->getComment(), DeeplTranslator::DEFAULT_LANGUAGE_CODE, SalesForecast::class, $salesForecast->getId());
            $salesForecast->setComment($translation);

            $subject = [] === $changeSet ? 'sfr.subject.comment' : 'sfr.subject.edition';
            $this->serviceLocator->get(SalesForecastNotifier::class)->sendEmail($salesForecast, $subject, 'sales_forecast_edition.html.twig', $changeSet);
        }
    }

    public function afterSalesForecastEdition(ViewEvent $event): void
    {
        $salesForecast = $event->getControllerResult();

        if (
            !$salesForecast instanceof SalesForecast
            || !$event->getRequest()->isMethod(Request::METHOD_PUT)
        ) {
            return;
        }

        $message = $salesForecast->getComment();

        if ($salesForecast->isNotificationRestricted()) {
            $message = SalesForecast::RESTRICTED_COMMENT_PREFIX.$message;
        }

        if (null !== $message) {
            $this->serviceLocator->get(CommentRequestManager::class)->insertComment($salesForecast, $message);
        }
    }

    public function onSalesForecastSynchronizedEdition(ViewEvent $event): void
    {
        $salesForecast = $event->getControllerResult();

        if (
            !$salesForecast instanceof SalesForecast
            || !$event->getRequest()->isMethod(Request::METHOD_PUT)
        ) {
            return;
        }

        // if synchronization was required
        // and the SFR is the only one in the MSFR
        // the other method must handle it
        if (
            !$salesForecast->isSynchronized()
            || null === $salesForecast->getMasterSalesForecast()
            || 1 === $salesForecast->getMasterSalesForecast()->getSalesForecasts()->count()
        ) {
            return;
        }

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $changeSet = $this->serviceLocator->get(EmailChangeSetFactory::class)->createChangeSetForEmail($salesForecast, ['masterSalesForecast', 'lastComment', 'delinquent', 'lastCommentedAt', 'closureNotificationSentAt']);

        $message = $salesForecast->getComment();

        foreach ($salesForecast->getMasterSalesForecast()->getSalesForecasts() as $siblingSalesForecast) {
            if ($salesForecast === $siblingSalesForecast) {
                continue;
            }

            if (!\in_array($siblingSalesForecast->getStatus(), SalesForecast::OPEN_STATUSES, true)) {
                continue;
            }

            if (null !== $message) {
                $this->serviceLocator->get(CommentRequestManager::class)->insertComment($siblingSalesForecast, $message);
            }

            foreach ([
                'status',
                'asm',
                'equoteId',
                'buyer',
                'endUser',
                'thirdParty',
                'airport',
                'estimatedSaleDate',
                'customerSuccessPercentage',
                'successPercentage',
                'delinquent',
            ] as $property) {
                if (\array_key_exists($property, $changeSet)) {
                    $propertyAccessor = $this->serviceLocator->get(PropertyAccessorInterface::class);
                    $propertyAccessor->setValue($siblingSalesForecast, $property, $propertyAccessor->getValue($salesForecast, $property));
                }
            }

            $em->persist($siblingSalesForecast);
        }

        $em->flush();

        if ($salesForecast->getMasterSalesForecast()->getSalesForecasts()->count() > 1) {
            $this->serviceLocator->get(SalesForecastNotifier::class)->sendMasterEdition($salesForecast, $changeSet);

            return;
        }
    }

    public function afterSalesForecastCreation(ViewEvent $event): void
    {
        $masterSalesForecast = $event->getControllerResult();

        if (!$masterSalesForecast instanceof MasterSalesForecast || !$event->getRequest()->isMethod(Request::METHOD_POST)) {
            return;
        }

        foreach ($masterSalesForecast->getSalesForecasts() as $salesForecast) {
            $message = $salesForecast->getComment();
            if (null !== $message) {
                $this->serviceLocator->get(CommentRequestManager::class)->insertComment($salesForecast, $message);
            }

            $this->serviceLocator->get(SalesForecastNotifier::class)->sendEmail($salesForecast, 'sfr.subject.creation', 'sales_forecast_creation.html.twig');
        }
    }

    public function beforeSalesForecastDeletion(ViewEvent $event): void
    {
        $salesForecast = $event->getRequest()->attributes->get('data');

        if (!$salesForecast instanceof SalesForecast || !$event->getRequest()->isMethod(Request::METHOD_DELETE)) {
            return;
        }

        $this->purgeForecastClosures($salesForecast);
    }

    public function afterSalesForecastDeletion(ViewEvent $event): void
    {
        $salesForecast = $event->getRequest()->attributes->get('data');

        if (!$salesForecast instanceof SalesForecast || !$event->getRequest()->isMethod(Request::METHOD_DELETE)) {
            return;
        }

        $masterSalesForecast = $salesForecast->getMasterSalesForecast();

        if (!$masterSalesForecast instanceof MasterSalesForecast) {
            return;
        }

        if ($masterSalesForecast->getSalesForecasts()->isEmpty()) {
            $em = $this->serviceLocator->get(EntityManagerInterface::class);
            $em->remove($masterSalesForecast);
            $em->flush();
        }
    }

    private function purgeForecastClosures(SalesForecast $salesForecast, bool $delete = false): void
    {
        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        foreach ($salesForecast->getForecastClosures() as $forecastClosure) {
            foreach ($forecastClosure->getCompetitorPricings() as $competitorPricing) {
                $competitorPricing->setForecastClosure(null);
                $em->persist($competitorPricing);
                $em->flush();
            }
            if ($delete) {
                $em->remove($forecastClosure);
            }
        }
        $em->flush();
    }
}
