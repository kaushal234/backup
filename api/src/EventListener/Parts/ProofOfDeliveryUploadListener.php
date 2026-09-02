<?php

declare(strict_types=1);

namespace App\EventListener\Parts;

use App\Entity\Parts\SBSparePartsRequest;
use App\Entity\Parts\SparePartsRequest;
use App\Event\FileUploadedEvent;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\SparePartsRequestManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ProofOfDeliveryUploadListener implements EventSubscriberInterface, ServiceSubscriberInterface
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
            FileUploadedEvent::class => ['onFileUpload'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            SparePartsRequestManager::class,
            EntityManagerInterface::class,
        ];
    }

    public function onFileUpload(FileUploadedEvent $event)
    {
        $sparePartsRequest = $event->getObject();

        if (!$sparePartsRequest instanceof SparePartsRequest) {
            return;
        }

        $previousStatus = $sparePartsRequest->getStatus();
        $sparePartsRequest->setStatus(SparePartsRequest::STATUS_CLOSED);
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $entityManager->persist($sparePartsRequest);
        $entityManager->flush();

        if ($sparePartsRequest instanceof SBSparePartsRequest) {
            $this->serviceLocator->get(SparePartsRequestManager::class)->handleSBSparePartsRequestClosing($sparePartsRequest, $previousStatus);
        }
    }
}
