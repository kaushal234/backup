<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\Directory\People;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Model\Task;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskManager
{
    private readonly ValidatorInterface $validator;
    private readonly Connection $legacyConnection;
    private readonly TaskCcManager $taskCcManager;
    private readonly TaskCommentsManager $taskCommentsManager;

    public function __construct(ValidatorInterface $validator, Connection $legacyConnection, TaskCcManager $taskCcManager, TaskCommentsManager $taskCommentsManager)
    {
        $this->validator = $validator;
        $this->legacyConnection = $legacyConnection;
        $this->taskCcManager = $taskCcManager;
        $this->taskCommentsManager = $taskCommentsManager;
    }

    /**
     * @throws Exception
     */
    public function insert(Task $task): int
    {
        $violations = $this->validator->validate($task);

        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $queryBuilder = $this->prepareQuery($task);

        $stmt = $this->legacyConnection->prepare($queryBuilder->getSQL());

        foreach ($queryBuilder->getParameters() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->executeStatement();

        $id = (int) $this->legacyConnection->lastInsertId();

        $task->setId($id);

        $this->taskCcManager->addCc($task);

        return $id;
    }

    public function getTask(int $taskId)
    {
        $qb = $this->legacyConnection->createQueryBuilder();
        $qb
            ->select('tasks.*')
            ->from('tasks')
            ->where('tasks.id = :id')
            ->setParameter('id', $taskId)
        ;

        $result = $this->legacyConnection->executeQuery($qb->getSQL(), $qb->getParameters());

        return $result->fetchAssociative();
    }

    public function close(Task $task, string $closeComment, People $people)
    {
        $qb = $this->legacyConnection->createQueryBuilder();
        $qb
            ->update('tasks')
            ->set('status', ':status')
            ->where('id = :id')
            ->setParameters(['id' => $task->getId(), 'status' => 'CLOSED'])
        ;

        $this->legacyConnection->executeQuery($qb->getSQL(), $qb->getParameters());

        $this->taskCommentsManager->insertComment($task, $closeComment, $people);
    }

    public function addComment(Task $task, string $comment, People $people)
    {
        $this->taskCommentsManager->insertComment($task, $comment, $people);
    }

    public function updateStatus(int $id, string $status)
    {
        $qb = $this->legacyConnection->createQueryBuilder();
        $qb
            ->update('tasks')
            ->set('status', ':status')
            ->where('id = :id')
            ->setParameters(['id' => $id, 'status' => $status])
        ;

        $this->legacyConnection->executeQuery($qb->getSQL(), $qb->getParameters());
    }

    public function findOpenTasksByModule(string $module, ?int $parentId = null, ?string $description = null): array
    {
        $qb = $this->legacyConnection->createQueryBuilder();
        $qb
            ->select('tasks.id')
            ->addSelect('tasks.parent_id')
            ->from('tasks')
            ->where('status = :status')
            ->andWhere('module = :module')
            ->setParameter('status', 'OPEN')
            ->setParameter('module', $module)
        ;

        if (null !== $parentId) {
            $qb
                ->andWhere('parent_id = :parentId')
                ->setParameter('parentId', $parentId)
            ;
        }
        if (null !== $description) {
            $qb
                ->andWhere('task = :description')
                ->setParameter('description', $description)
            ;
        }

        $result = $this->legacyConnection->executeQuery($qb->getSQL(), $qb->getParameters());

        return $result->fetchAllAssociative();
    }

    public function findTasksByDescriptionAndDateRange(
        string $module,
        string $description,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $qb = $this->legacyConnection->createQueryBuilder();

        $qb
            ->select('*')
            ->from('tasks')
            ->where('module = :module')
            ->andWhere('task LIKE :description')
            ->andWhere('date BETWEEN :startDate AND :endDate')
            ->setParameters([
                'module' => $module,
                'description' => $description,
                'startDate' => $startDate->format('Y-m-d H:i:s'),
                'endDate' => $endDate->format('Y-m-d H:i:s'),
            ])
        ;

        $result = $this->legacyConnection->executeQuery(
            $qb->getSQL(),
            $qb->getParameters()
        );

        return $result->fetchAllAssociative();
    }

    private function prepareQuery(Task $task)
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $transform = new Utf8ToHtmlEntities();

        $queryBuilder
            ->insert('tasks')
            ->setValue('module', ':module')
            ->setValue('task', ':description')
            ->setValue('parent_id', ':parentId')
            ->setValue('status', ':status')
            ->setValue('assignee', ':assignee')
            ->setValue('assignor', ':assignor')
            ->setValue('assignor', ':assignor')
            ->setValue('erp', ':erp')
            ->setValue('bu_id', ':businessUnit')
            ->setValue('date', ':date')
            ->setValue('due_date', ':dueDate')
            ->setValue('escalation_trigger', ':escalationTrigger')
            ->setParameters([
                'module' => $task->getModule(),
                'status' => 'OPEN',
                'description' => $transform($task->getDescription(), []),
                'parentId' => $task->getParentId() ?? 0,
                'assignee' => $task->getAssignee()->getLegacyId(),
                'assignor' => $task->getAssignor()->getLegacyId(),
                'erp' => $task->getLocation()->getErp(),
                'businessUnit' => $task->getLocation()->getBusinessUnit()->getLegacyId(),
                'date' => $task->getDate()->format('Y-m-d H:m:s'),
                'dueDate' => $task->getDueDate()->format('Y-m-d H:m:s'),
                'escalationTrigger' => $task->getEscalationTrigger(),
            ])
        ;

        return $queryBuilder;
    }
}
