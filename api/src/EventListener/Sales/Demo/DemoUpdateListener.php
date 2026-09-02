<?php

declare(strict_types=1);

namespace App\EventListener\Sales\Demo;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\EmissionRating;
use App\Entity\Sales\Demo;
use App\Event\Activity\CommentCreatedEvent;
use App\Manager\Manufacturing\IntelligentBatterySystemManager;
use App\Notifier\Sales\Demo\DemoNotifier;
use App\Request\Activity\CommentRequestManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class DemoUpdateListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => ['removeDelinquencyWhenCommentPosted'],
            KernelEvents::VIEW => [
                ['onDemoCommentCreation', EventPriorities::POST_WRITE],
                ['onDemoCommentEdition', EventPriorities::PRE_WRITE],
                ['onDemoStatusUpdate', EventPriorities::PRE_WRITE],
                ['onDemoRevisedEndDateUpdate', EventPriorities::POST_WRITE],
            ],
        ];
    }

    public function removeDelinquencyWhenCommentPosted(CommentCreatedEvent $event)
    {
        $item = $event->getItem();

        if (!$item instanceof Demo) {
            return;
        }

        if (!$item->isDelinquent()) {
            return;
        }

        $item->setDelinquent(false);
        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $em->persist($item);
        $em->flush();
    }

    public function onDemoCommentCreation(ViewEvent $event)
    {
        $result = $event->getControllerResult();

        if (!$result instanceof Demo || Request::METHOD_POST !== $event->getRequest()->getMethod()) {
            return;
        }

        $message = $result->getComment();

        if (null !== $message) {
            $this->serviceLocator->get(CommentRequestManager::class)->insertComment($result, $message);
        }
    }

    public function onDemoCommentEdition(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        /** @var Operation|null $operation */
        $operation = $event->getRequest()->attributes->get('_api_operation');

        if (!$result instanceof Demo || Request::METHOD_PUT !== $event->getRequest()->getMethod() || (null !== $operation && 'update_demo_status' === $operation->getName())) {
            return;
        }

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $uow = $em->getUnitOfWork();
        $uow->computeChangeSets();

        $changeset = $uow->getEntityChangeSet($result);

        if (!isset($changeset['comment'])) {
            return;
        }

        $message = $result->getComment();

        if (null !== $message) {
            $em->persist($result);
            $em->flush();
            $this->serviceLocator->get(CommentRequestManager::class)->insertComment($result, $message);
        }

        $this->serviceLocator->get(DemoNotifier::class)->sendCommentEmail($result, $changeset['comment'][1]);
    }

    public function onDemoStatusUpdate(ViewEvent $event)
    {
        $demo = $event->getControllerResult();

        if (!$demo instanceof Demo || Request::METHOD_PUT !== $event->getRequest()->getMethod()) {
            return;
        }

        $previousDemo = $event->getRequest()->attributes->get('previous_data');

        if (!$previousDemo instanceof Demo) {
            return;
        }

        if ($previousDemo->getStatus() !== $demo->getStatus()) {
            if (Demo::ACTIVE === $demo->getStatus() && null === $demo->getEquipmentRecord()) {
                throw new BadRequestHttpException("Demo can't be set to ACTIVE without any ER set");
            }
            if (Demo::SUBMITTED !== $demo->getStatus()) {
                $this->serviceLocator->get(DemoNotifier::class)->sendStatusEmail($demo);
            }
        }

        if (
            $previousDemo->getEmissionRating() !== ($emissionRating = $demo->getEmissionRating())
            && null !== $emissionRating
            && \in_array($demo->getEmissionRating()->getName(), EmissionRating::INTELLIGENT_BATTERY_SYSTEMS, true)
        ) {
            $this->serviceLocator->get(IntelligentBatterySystemManager::class)->createTasks($demo->getAsm(), $demo->getSso(), $demo->getFactory(), 'DEMO', $demo->getId());
        }
    }

    public function onDemoRevisedEndDateUpdate(ViewEvent $event)
    {
        $result = $event->getControllerResult();

        if (!$result instanceof Demo || Request::METHOD_PUT !== $event->getRequest()->getMethod()) {
            return;
        }

        if (!$result->isDelinquent()) {
            return;
        }

        if ($result->getRevisedEndDate() <= new \DateTime('today')) {
            return;
        }

        if ($result->getLastCommentedAt() > new \DateTime('1 month ago')) {
            return;
        }

        $result->setDelinquent(false);

        $em = $this->serviceLocator->get(EntityManagerInterface::class);
        $em->persist($result);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            CommentRequestManager::class,
            EntityManagerInterface::class,
            DemoNotifier::class,
            IntelligentBatterySystemManager::class,
        ];
    }
}
