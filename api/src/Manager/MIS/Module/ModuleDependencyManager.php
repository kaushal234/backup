<?php

declare(strict_types=1);

namespace App\Manager\MIS\Module;

use App\Entity\Common\Notification\NotificationTemplate;
use App\Entity\MIS\Project\Project;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\MIS\TroubleTicket\TypeDefaultAssignee;
use App\Entity\Module\Module;
use App\Entity\Task\Task;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

readonly class ModuleDependencyManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Connection $legacyConnection
    ) {
    }

    public function getLinkedObjects(Module $module): array
    {
        $dependencies = [];

        // Check Tasks
        $tasksCount = (int) $this->entityManager->getRepository(Task::class)
            ->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.module = :module')
            ->andWhere('t.status != :closedStatus')
            ->setParameter('module', $module)
            ->setParameter('closedStatus', Task::CLOSED)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if ($tasksCount > 0) {
            $dependencies[] = [
                'type' => 'tasks',
                'count' => $tasksCount,
                'label' => 'open task(s)',
            ];
        }

        // Check Trouble Tickets
        $troubleTicketsCount = (int) $this->entityManager->getRepository(TroubleTicket::class)
            ->createQueryBuilder('tt')
            ->select('COUNT(tt.id)')
            ->where('tt.module = :module')
            ->andWhere('tt.status NOT IN (:closedStatuses)')
            ->setParameter('module', $module)
            ->setParameter('closedStatuses', TroubleTicket::CLOSED_STATUSES)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if ($troubleTicketsCount > 0) {
            $dependencies[] = [
                'type' => 'troubleTickets',
                'count' => $troubleTicketsCount,
                'label' => 'open trouble ticket(s)',
            ];
        }

        // Check Legacy Tasks
        $legacyTasksCount = $this->countOpenLegacyTasks($module);

        if ($legacyTasksCount > 0) {
            $dependencies[] = [
                'type' => 'legacyTasks',
                'count' => $legacyTasksCount,
                'label' => 'open legacy task(s)',
            ];
        }

        // Check Projects
        $projectsCount = (int) $this->entityManager->getRepository(Project::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.module = :module')
            ->andWhere('p.status NOT IN (:closedStatuses)')
            ->setParameter('module', $module)
            ->setParameter('closedStatuses', [Project::CLOSED, Project::CANCELLED])
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if ($projectsCount > 0) {
            $dependencies[] = [
                'type' => 'projects',
                'count' => $projectsCount,
                'label' => 'open project(s)',
            ];
        }

        // Check NotificationTemplates
        $notificationTemplatesCount = (int) $this->entityManager->getRepository(NotificationTemplate::class)
            ->createQueryBuilder('nt')
            ->select('COUNT(nt.id)')
            ->where('nt.module = :module')
            ->setParameter('module', $module)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if ($notificationTemplatesCount > 0) {
            $dependencies[] = [
                'type' => 'notificationTemplates',
                'count' => $notificationTemplatesCount,
                'label' => 'notification template(s)',
            ];
        }

        // Check TypeDefaultAssignees
        $typeDefaultAssigneesCount = (int) $this->entityManager->getRepository(TypeDefaultAssignee::class)
            ->createQueryBuilder('tda')
            ->select('COUNT(tda.id)')
            ->where('tda.module = :module')
            ->setParameter('module', $module)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        if ($typeDefaultAssigneesCount > 0) {
            $dependencies[] = [
                'type' => 'typeDefaultAssignees',
                'count' => $typeDefaultAssigneesCount,
                'label' => 'default assignee(s)',
            ];
        }

        // Check module dependencies (requiringModules)
        $requiringModulesCount = $module->getRequiringModules()->filter(
            static fn (Module $m) => Module::ACTIVE === $m->status
        )->count()
        ;

        if ($requiringModulesCount > 0) {
            $dependencies[] = [
                'type' => 'requiringModules',
                'count' => $requiringModulesCount,
                'label' => 'requiring module(s)',
            ];
        }

        return $dependencies;
    }

    public function canDisable(Module $module): bool
    {
        return 0 === \count($this->getLinkedObjects($module));
    }

    public function getDisableErrorMessage(Module $module): ?string
    {
        $dependencies = $this->getLinkedObjects($module);

        if (empty($dependencies)) {
            return null;
        }

        $linkedItems = [];
        foreach ($dependencies as $dependency) {
            $linkedItems[] = \sprintf('%d %s', $dependency['count'], $dependency['label']);
        }

        return \sprintf(
            'Disable module "%s" is impossible because link exists with : %s. Please update those items first.',
            $module->getName(),
            implode(', ', $linkedItems)
        );
    }

    private function countOpenLegacyTasks(Module $module): int
    {
        $qb = $this->legacyConnection->createQueryBuilder();
        $qb
            ->select('COUNT(tasks.id)')
            ->from('tasks')
            ->where('status = :status')
            ->andWhere('module = :module')
            ->setParameter('status', 'OPEN')
            ->setParameter('module', $module->getName())
        ;

        $result = $this->legacyConnection->executeQuery($qb->getSQL(), $qb->getParameters());

        return (int) $result->fetchOne();
    }
}
