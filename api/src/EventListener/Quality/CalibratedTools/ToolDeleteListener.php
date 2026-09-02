<?php

declare(strict_types=1);

namespace App\EventListener\Quality\CalibratedTools;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Quality\CalibratedTools\Tool;
use App\Workflow\WorkflowStatusUpdater;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Workflow\Exception\LogicException;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ToolDeleteListener implements EventSubscriberInterface, ServiceSubscriberInterface
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
                ['onDelete', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public function onDelete(ViewEvent $event)
    {
        $tool = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$tool instanceof Tool || !$request->isMethod(Request::METHOD_DELETE)) {
            return;
        }

        try {
            $this->serviceLocator->get(WorkflowStatusUpdater::class)->applyStatus($tool, Tool::SCRAPPED);
        } catch (LogicException $logicException) {
            throw new BadRequestHttpException(\sprintf('Status %s is not allowed', Tool::SCRAPPED), $logicException);
        }

        $tool->setSerialNumber(null);
        $entityManager = $this->serviceLocator->get(EntityManagerInterface::class);
        $entityManager->persist($tool);
        $entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return [
            EntityManagerInterface::class,
            WorkflowStatusUpdater::class,
        ];
    }
}
