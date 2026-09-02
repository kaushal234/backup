<?php

declare(strict_types=1);

namespace App\EventListener\Quality\CalibratedTools;

use App\Entity\Quality\CalibratedTools\CalibrationLog;
use App\Event\FileUploadedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class CalibrationLogCreationListener implements EventSubscriberInterface, ServiceSubscriberInterface
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

    public function onFileUpload(FileUploadedEvent $event)
    {
        $calibrationLog = $event->getObject();
        $metadata = $event->getMetadata();

        if (!$calibrationLog instanceof CalibrationLog || !isset($metadata['calibration_date'])) {
            return;
        }

        $calibrationLog->setCalibrationDate(new \DateTime($metadata['calibration_date']));
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $entityManager->persist($calibrationLog);
        $entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
        ];
    }
}
