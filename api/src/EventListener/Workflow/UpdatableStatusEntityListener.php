<?php

declare(strict_types=1);

namespace App\EventListener\Workflow;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\UpdatableStatusEntityInterface;
use App\Workflow\WorkflowStatusUpdater;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class UpdatableStatusEntityListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['changeStatus', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public function changeStatus(ViewEvent $event): void
    {
        $entity = $event->getControllerResult();

        if (!$entity instanceof UpdatableStatusEntityInterface) {
            return;
        }

        $request = $event->getRequest();

        if ($request->attributes->get('_faq_status_override')) {
            return;
        }

        if (Request::METHOD_PUT !== $request->getMethod() && (Request::METHOD_POST === $request->getMethod() && !\in_array($request->attributes->get('_route'), ['transfer_trouble_ticket', 'reopen_trouble_ticket', 'comment_trouble_ticket', 'task_close_comment', 'task_transfer', 'task_reopen', 'mis_project_status', 'mis_project_close', 'task_pause_comment', 'task_comment'], true))) {
            return;
        }

        $previousData = $request->attributes->get('previous_data');
        if (!$previousData instanceof UpdatableStatusEntityInterface) {
            return;
        }

        if ($previousData->getStatus() === $entity->getStatus()) {
            return;
        }

        $newStatus = $entity->getStatus();
        $entity->setStatus($previousData->getStatus());

        try {
            $this->serviceLocator->get(WorkflowStatusUpdater::class)->applyStatus($entity, $newStatus);
        } catch (\LogicException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            WorkflowStatusUpdater::class,
        ];
    }
}
