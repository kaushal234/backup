<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\MIS\GuestUser\GuestUser;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use LegacyBundle\Doctrine\Transformer\Utf8ToHtmlEntities;
use LegacyBundle\Model\Sequence;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SequenceManager
{
    /**
     * @var string
     */
    final public const COMMENT_START_OF_SEQUENCE = 'Start of sequence';
    private readonly ValidatorInterface $validator;

    private readonly Connection $legacyConnection;

    private readonly TaskCommentsManager $commentsManager;

    private readonly TaskCcManager $taskCcManager;

    public function __construct(ValidatorInterface $validator, Connection $legacyConnection, TaskCommentsManager $commentsManager, TaskCcManager $taskCcManager)
    {
        $this->validator = $validator;
        $this->legacyConnection = $legacyConnection;
        $this->commentsManager = $commentsManager;
        $this->taskCcManager = $taskCcManager;
    }

    public function insert(Sequence $sequence): int
    {
        $violations = $this->validator->validate($sequence);
        if ($violations->count() > 0) {
            throw new ValidationException($violations);
        }

        $qb = $this->legacyConnection->createQueryBuilder();

        $this->prepareQuery($sequence, $qb);

        $this->legacyConnection->executeQuery($qb->getSQL(), $qb->getParameters());

        $id = (int) $this->legacyConnection->lastInsertId();

        $sequence->setId($id);

        $this->commentsManager->insertComment($sequence, self::COMMENT_START_OF_SEQUENCE);

        $this->taskCcManager->addCc($sequence);

        return $id;
    }

    public function getTemplate(string $name): array
    {
        $templateQuery = $this->legacyConnection->createQueryBuilder();

        $templateQuery
            ->select('tpl.id', 'tpl.def_escalation_trigger AS escalation_trigger', 'tpl.short_desc')
            ->from('cal_seq_tpl', 'tpl')
            ->where('tpl.name = :name')
            ->setParameter('name', $name)
        ;

        $result = $this->legacyConnection->executeQuery($templateQuery->getSQL(), $templateQuery->getParameters());

        return $result->fetchAssociative();
    }

    public function findOpenSequence(string $template, int $parentId)
    {
        $query = $this->legacyConnection->createQueryBuilder();

        $query
            ->select('seq.id')
            ->from('tasks', 'seq')
            ->leftJoin('seq', 'cal_seq_tpl', 'seq_tpl', 'seq_tpl.id = seq.tplno')
            ->where('seq.module = :module')
            ->andWhere('seq.status = :status')
            ->andWhere('seq.parent_id = :parent_id')
            ->andWhere('seq_tpl.name = :template')
            ->setParameter('module', 'SEQ')
            ->setParameter('status', 'OPEN')
            ->setParameter('parent_id', $parentId)
            ->setParameter('template', $template)
        ;

        $result = $this->legacyConnection->executeQuery(
            $query->getSQL(),
            $query->getParameters(),
            $query->getParameterTypes()
        );

        return $result->fetchAssociative();
    }

    public function findApprovedSequencesForGuestUser(GuestUser $guestUser): array
    {
        $lastCommentStatusSubQuery = $this->legacyConnection->createQueryBuilder()
            ->select('tc.status')
            ->from('tasks_comments', 'tc')
            ->where('tc.parent_id = seq.id')
            ->orderBy('tc.id', 'DESC')
            ->setMaxResults(1);

        $queryBuilder = $this->legacyConnection->createQueryBuilder();

        $queryBuilder
            ->select('seq.id')
            ->from('tasks', 'seq')
            ->leftJoin('seq', 'cal_seq_tpl', 'seq_tpl', 'seq_tpl.id = seq.tplno')
            ->where('seq_tpl.name  = :template')
            ->andWhere('seq.status = :status')
            ->andWhere(\sprintf('(%s) <> :cancel', $lastCommentStatusSubQuery->getSQL()))
            ->andWhere('seq.parent_id = :user_id')
            ->setParameter('template', 'guest_user.new')
            ->setParameter('status', 'CLOSED')
            ->setParameter('cancel', 'CANCEL')
            ->setParameter('user_id', $guestUser->getId())
        ;

        $result = $this->legacyConnection->executeQuery(
            $queryBuilder->getSQL(),
            $queryBuilder->getParameters(),
            $queryBuilder->getParameterTypes()
        );

        return $result->fetchAllAssociative();
    }

    private function prepareQuery(Sequence $sequence, QueryBuilder $qb)
    {
        $template = $this->getTemplate($sequence->getTemplateName());

        if ([] === $template) {
            throw new \InvalidArgumentException(\sprintf('Template %s was not found', $sequence->getTemplateName()));
        }

        $sequence->setTemplateDescription((string) $template['short_desc']);

        $transform = new Utf8ToHtmlEntities();

        $qb
            ->insert('tasks')
            ->setValue('module', ':module')
            ->setValue('seq', ':seq')
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
            ->setValue('close_params', ':closeParams')
            ->setValue('seq_mode', ':mode')
            ->setValue('tplno', ':templateNumber')
            ->setValue('escalation_trigger', ':escalationTrigger')
            ->setValue('cur_step', ':currentStep')
            ->setParameters([
                'module' => $sequence->getModule(),
                'seq' => 'Y',
                'status' => 'OPEN',
                'description' => $transform($sequence->getDescription(), []),
                'parentId' => $sequence->getParentId() ?? 0,
                'assignee' => $sequence->getAssignee()->getLegacyId(),
                'assignor' => $sequence->getAssignor()->getLegacyId(),
                'erp' => $sequence->getLocation()->getErp(),
                'businessUnit' => $sequence->getLocation()->getLegacyId(),
                'date' => $sequence->getDate()->format('Y-m-d H:m:s'),
                'dueDate' => $sequence->getDueDate()->format('Y-m-d H:m:s'),
                'closeParams' => base64_encode(serialize($sequence->getCloseParams())),
                'mode' => $sequence->getMode(),
                'templateNumber' => $template['id'],
                'currentStep' => 1,
                'escalationTrigger' => $template['escalation_trigger'],
            ])
        ;
    }
}
