<?php

declare(strict_types=1);

namespace App\EventListener\Quality\CalibratedTools;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Quality\CalibratedTools\CalibrationLog;
use App\Entity\Quality\CalibratedTools\Tool;
use App\Notifier\Quality\CalibratedToolNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ToolUpdateListener implements EventSubscriberInterface, ServiceSubscriberInterface
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
                ['onUpdate', EventPriorities::PRE_WRITE],
                ['onStatusUpdate', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onUpdate(ViewEvent $event)
    {
        $tool = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$tool instanceof Tool || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $uow = $entityManager->getUnitOfWork();
        $uow->computeChangeSets();

        $changeSet = $uow->getEntityChangeSet($tool);

        if (
            (
                !\array_key_exists('calibrationInterval', $changeSet)
                && !\array_key_exists('calibrationNotice', $changeSet)
                && !\array_key_exists('nextCalibrationDate', $changeSet)
            ) || !\in_array($tool->getStatus(), [Tool::ACTIVE, Tool::CALIBRATION_DUE_SOON, Tool::EXPIRED], true)
        ) {
            return $tool;
        }

        // Here for security's sake, should not be triggered.
        if ($tool->getCalibrationLogs()->isEmpty()) {
            $tool->setStatus(Tool::OUT_OF_SERVICE);

            return $tool;
        }

        $now = new \DateTime();

        if (null !== $tool->getNextCalibrationDate()) {
            $expirationDate = $tool->getNextCalibrationDate();
            $notificationDate = (clone $tool->getNextCalibrationDate())->modify(\sprintf('-%d days', $tool->getCalibrationNotice()));
        } else {
            /** @var CalibrationLog $lastLog */
            $lastLog = $tool->getCalibrationLogs()->last();

            /** @var \DateTime $logDate */
            $logDate = $lastLog->getEndDate();

            $expirationDate = (clone $logDate)->modify(\sprintf('+%d days', $tool->getCalibrationInterval()));
            $notificationDate = (clone $logDate)->modify(\sprintf('+%d days', $tool->getCalibrationInterval() - $tool->getCalibrationNotice()));
        }

        $mailer = $this->serviceLocator->get(CalibratedToolNotifier::class);
        if ($now >= $expirationDate) {
            $tool->setStatus(Tool::EXPIRED);
            $mailer->sendEmail([$tool], $tool->getLocationArea()->getSupervisor()->getEmail());
        } elseif ($now >= $notificationDate) {
            $tool->setStatus(Tool::CALIBRATION_DUE_SOON);
            $mailer->sendEmail([$tool], $tool->getLocationArea()->getSupervisor()->getEmail());
        } else {
            $tool->setStatus(Tool::ACTIVE);
        }
    }

    public function onStatusUpdate(ViewEvent $event)
    {
        $tool = $event->getControllerResult();
        $request = $event->getRequest();
        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');

        if (!$tool instanceof Tool || !$request->isMethod(Request::METHOD_PUT) || 'tool_status' !== $operation->getName()) {
            return;
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        if (Tool::UNDER_CALIBRATION === $tool->getStatus()) {
            $calibrationLog = (new CalibrationLog())
                ->setTool($tool)
                ->setStartDate(new \DateTime());
            $tool->addCalibrationLog($calibrationLog);

            $entityManager->persist($tool);
            $entityManager->flush();
        }

        if (\in_array($tool->getStatus(), [Tool::UNDER_CALIBRATION, Tool::ACTIVE], true)) {
            /** @var CalibrationLog $log */
            $log = $tool->getCalibrationLogs()->last();
            $log->setEndDate(new \DateTime());

            $entityManager->persist($tool);
            $entityManager->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            CalibratedToolNotifier::class,
        ];
    }
}
