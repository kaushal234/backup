<?php

declare(strict_types=1);

namespace App\EventListener\Purchasing\SupplierRanking;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Purchasing\SupplierRanking\Classification;
use App\Entity\Purchasing\SupplierRanking\SupplierRanking;
use App\Manager\Purchasing\SupplierRanking\ThresholdsManager;
use App\Notifier\Purchasing\SupplierRanking\SupplierRankingNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * This listener update classification depending on notations.
 */
class SupplierRankingListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['updateClassification', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function updateClassification(RequestEvent $event)
    {
        $supplierRanking = $event->getRequest()->attributes->get('data');
        $previousSupplierRanking = $event->getRequest()->attributes->get('previous_data');

        if (!$supplierRanking instanceof SupplierRanking || Request::METHOD_PUT !== $event->getRequest()->getMethod()) {
            return;
        }

        // Always update last review date when update a supplier ranking.
        $supplierRanking->lastReviewAt = new \DateTime();

        // Automatically update classification on supplier ranking update.
        /** @var Classification $newClassification */
        $newClassification = $this->serviceLocator->get(ThresholdsManager::class)->findNewClassification($supplierRanking->expertiseLevel, $supplierRanking->getNotations());
        // Avoid update if the new classificatin is part of his workflow/targets.
        if (!$newClassification->getTargetClassifications()->contains($supplierRanking->classification)) {
            $supplierRanking->classification = $newClassification;
        }

        // Update lastScreeningBy only when last screening date is different.
        if ($previousSupplierRanking->lastScreeningAt?->getTimestamp() !== $supplierRanking->lastScreeningAt?->getTimestamp()) {
            $supplierRanking->lastScreeningBy = $this->serviceLocator->get(Security::class)->getUser();
        }

        // Notifier users when a supplier ranking become unnaproved.
        if (!$supplierRanking->isSupplierApproved() && $previousSupplierRanking->isSupplierApproved()) {
            $this->serviceLocator->get(SupplierRankingNotifier::class)->sendOnDecreasedClassification($supplierRanking, $previousSupplierRanking);
        }

        $this->serviceLocator->get(EntityManagerInterface::class)->flush();
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            ThresholdsManager::class,
            Security::class,
            SupplierRankingNotifier::class,
        ];
    }
}
