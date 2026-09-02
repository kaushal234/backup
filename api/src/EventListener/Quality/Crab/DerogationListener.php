<?php

declare(strict_types=1);

namespace App\EventListener\Quality\Crab;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Directory\People;
use App\Entity\Quality\Crab;
use App\Entity\Quality\Derogation;
use App\Factory\Common\Notification\Quality\DerogationDecisionUpdateNotificationFactory;
use App\Notifier\Quality\Crab\CrabNotifier;
use App\Repository\Directory\PeopleRepository;
use App\Request\Activity\CommentRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class DerogationListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onPreCreate', EventPriorities::PRE_WRITE],
                ['onPreUpdate', EventPriorities::PRE_WRITE],
                ['onPostCreate', EventPriorities::POST_WRITE],
                ['onPostUpdate', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function onPreCreate(ViewEvent $event): void
    {
        $derogation = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$derogation instanceof Derogation || Request::METHOD_POST !== $request->getMethod()) {
            return;
        }

        /** @var Crab $crab */
        $crab = $derogation->getCrabs()->first();
        if (null === ($location = $crab->equipmentRecord->getManufacturerLocation())) {
            throw new BadRequestHttpException('ER has no manufacturer location set, derogation not possible.');
        }

        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        if (null === $derogation->assignee) {
            /** @var PeopleRepository $peopleRepository */
            $peopleRepository = $entityManager->getRepository(People::class);

            if (null === ($assignee = $peopleRepository->findOneByPositionByLocation($crab->department->derogationPosition, $location))) {
                throw new BadRequestHttpException(\sprintf('Action not possible, no %s found on %s', $crab->department->derogationPosition->getCode(), $location->getName()));
            }

            $derogation->assignee = $assignee;
        }

        $derogation->dueDate = (new \DateTime())->modify('+5 days');
        $crab->status = Crab::FOR_DEROGATION;

        $entityManager->persist($derogation);
        $entityManager->persist($crab);

        $entityManager->flush();

        $this->serviceLocator->get(CrabNotifier::class)->sendDerogation($derogation, 'create');
    }

    public function onPostCreate(ViewEvent $event): void
    {
        $derogation = $event->getControllerResult();
        $request = $event->getRequest();
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        if (!$derogation instanceof Derogation || Request::METHOD_POST !== $request->getMethod()) {
            return;
        }

        if ($derogation->assignee) {
            $notification = $this->serviceLocator->get(DerogationDecisionUpdateNotificationFactory::class)->createNotification($derogation, $derogation->assignee);
            $entityManager->persist($notification);
            $entityManager->flush();
        }
    }

    public function onPostUpdate(ViewEvent $event): void
    {
        $derogation = $event->getControllerResult();
        $request = $event->getRequest();
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);

        if (!$derogation instanceof Derogation || Request::METHOD_PUT !== $request->getMethod()) {
            return;
        }

        $previousData = $request->attributes->get('previous_data');
        if (!$previousData instanceof Derogation) {
            return;
        }

        if (null !== $derogation->comment) {
            $this->serviceLocator->get(CommentRequestManager::class)->insertComment($derogation, $derogation->comment);
        }

        if ($previousData->assignee !== $derogation->assignee) {
            $this->serviceLocator->get(CrabNotifier::class)->sendDerogation($derogation, 'transfer');
            $notification = $this->serviceLocator->get(DerogationDecisionUpdateNotificationFactory::class)->createNotification($derogation, $derogation->assignee);
            $entityManager->persist($notification);
            $entityManager->flush();
        }
    }

    public function onPreUpdate(ViewEvent $event): void
    {
        $derogation = $event->getControllerResult();
        $request = $event->getRequest();

        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        if (!$derogation instanceof Derogation || Request::METHOD_PUT !== $request->getMethod() || 'update_derogation_status' === $operation->getName()) {
            return;
        }

        if (\in_array($derogation->getStatus(), [Derogation::ACCEPTED, Derogation::DENIED, Derogation::ARCHIVED], true)) {
            throw new BadRequestHttpException('Not possible to edit a closed or archived derogation.');
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            CrabNotifier::class,
            CommentRequestManager::class,
            DerogationDecisionUpdateNotificationFactory::class,
        ];
    }
}
