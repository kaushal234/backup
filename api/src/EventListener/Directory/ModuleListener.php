<?php

declare(strict_types=1);

namespace App\EventListener\Directory;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Module\Module;
use App\Request\Activity\CommentRequestManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ModuleListener implements EventSubscriberInterface, ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function onUpdate(ViewEvent $event): void
    {
        $module = $event->getControllerResult();
        $request = $event->getRequest();

        if (!$module instanceof Module || !$request->isMethod(Request::METHOD_PUT)) {
            return;
        }

        $unitOfWork = $this->serviceLocator->get(EntityManagerInterface::class)->getUnitOfWork();
        $previousModule = $unitOfWork->getOriginalEntityData($module);

        $legacyConnection = $this->serviceLocator->get('doctrine.dbal.legacy_connection');
        $legacyConnection->beginTransaction();

        if ($module->transferMisOwner && $module->getMisOwner() !== $previousModule['misOwner']) {
            $updateDevQueryBuilder = $legacyConnection->createQueryBuilder();
            $updateDevQueryBuilder
                ->update('tasks')
                ->set('assignee', ':newAssignee')
                ->where('module = :module')
                ->andWhere('status != :status')
                ->andWhere('assignee = :oldAssignee')
                ->andWhere('ticket_module_id = :moduleId')
                ->setParameters([
                    'newAssignee' => $module->getMisOwner()->getLegacyId(),
                    'module' => 'TTS',
                    'status' => 'CLOSED',
                    'oldAssignee' => $previousModule['misOwner']->getLegacyId(),
                    'moduleId' => $module->getLegacyId(),
                ])
            ;
            $updateDevQueryBuilder->execute();

            if (null !== $module->getMisOwner()) {
                $this->serviceLocator->get(CommentRequestManager::class)->insertComment($module, \sprintf('All opened tickets assigned to %s have been assigned to %s', (string) $previousModule['misOwner'], (string) $module->getMisOwner()));
            }
        }
        if ($module->transferOperationalOwner && $module->getOperationalOwner() !== $previousModule['operationalOwner']) {
            $updateDevQueryBuilder = $legacyConnection->createQueryBuilder();
            $updateDevQueryBuilder
                ->update('tasks')
                ->set('assignee', ':newAssignee')
                ->where('module = :module')
                ->andWhere('status != :status')
                ->andWhere('assignee = :oldAssignee')
                ->andWhere('ticket_module_id = :moduleId')
                ->setParameters([
                    'newAssignee' => $module->getOperationalOwner()->getLegacyId(),
                    'module' => 'TTS',
                    'status' => 'CLOSED',
                    'oldAssignee' => $previousModule['operationalOwner']->getLegacyId(),
                    'moduleId' => $module->getLegacyId(),
                ])
            ;
            $updateDevQueryBuilder->execute();

            if (null !== $module->getOperationalOwner()) {
                $this->serviceLocator->get(CommentRequestManager::class)->insertComment($module, \sprintf('All opened tickets assigned to %s have been assigned to %s', (string) $previousModule['operationalOwner'], (string) $module->getOperationalOwner()));
            }
        }
        $legacyConnection->commit();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => [
                ['onUpdate', EventPriorities::PRE_WRITE],
            ],
        ];
    }

    public static function getSubscribedServices(): array
    {
        return [
            'doctrine.dbal.legacy_connection' => Connection::class,
            EntityManagerInterface::class,
            CommentRequestManager::class,
        ];
    }
}
