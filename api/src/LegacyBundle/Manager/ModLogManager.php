<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use App\Entity\Directory\People;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;

class ModLogManager
{
    private readonly Connection $legacyConnection;
    private readonly Security $security;

    public function __construct(Connection $legacyConnection, Security $security)
    {
        $this->legacyConnection = $legacyConnection;
        $this->security = $security;
    }

    public function updateLogParentIdentifier(int $legacyId, string $parentIdentifier): void
    {
        $qb = $this->legacyConnection->createQueryBuilder();
        $qb
            ->update('mod_logs')
            ->set('parent_id', ':parent_id')
            ->where('id = :legacy_id')
            ->setParameters([
                'legacy_id' => $legacyId,
                'parent_id' => $parentIdentifier,
            ])
        ;
        $this->legacyConnection->prepare($qb->getSQL())->executeQuery();
    }

    public function insertLog(int $legacyId, string $module, string $comment, ?People $user = null): void
    {
        /** @var People|null $user */
        $user = $user ?? $this->security->getUser();

        $qb = $this->legacyConnection->createQueryBuilder();
        $qb
            ->insert('mod_logs')
            ->setValue('parent_id', ':parent_id')
            ->setValue('module', ':module')
            ->setValue('date', ':date')
            ->setValue('poster', ':poster_id')
            ->setValue('comment', ':comment')
            ->setParameters([
                'parent_id' => $legacyId,
                'module' => $module,
                'date' => (new \DateTime())->format('Y-m-d H:i:s'),
                'poster_id' => $user?->getLegacyId(),
                'comment' => $comment,
            ])
        ;

        $qb->executeStatement();
    }

    public function findFirstEstimatedGreenTagDateLogs(): array
    {
        $qb = $this->legacyConnection->createQueryBuilder();

        return $qb
            ->select('ml.parent_id', 'ml.comment', 'ml.id', 'ml.date')
            ->from('mod_logs', 'ml')
            ->innerJoin(
                'ml',
                '(
                SELECT parent_id, MIN(id) AS first_log_id
                FROM mod_logs
                WHERE module = :module
                  AND (
                    comment REGEXP :new_format_regexp
                    OR comment REGEXP :old_format_regexp
                    OR comment REGEXP :html_format_regexp
                  )
                GROUP BY parent_id
            )',
                'first_logs',
                'first_logs.first_log_id = ml.id'
            )
            ->setParameter('module', 'ER')
            ->setParameter(
                'new_format_regexp',
                'estimatedGreenTagDate:[[:space:]]*(-0001-11-30 00:00:00|0000-00-00 00:00:00)?[[:space:]]*=>[[:space:]]*[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}'
            )
            ->setParameter(
                'old_format_regexp',
                '(Estimated GT Date updated from|Estimate GT Date Changed from)[[:space:]]*0000-00-00[[:space:]]*to[[:space:]]*([0-9]{4}-[0-9]{1,2}-[0-9]{1,2}|[0-9]{8}|[0-9]{4}/[0-9]{1,2}/[0-9]{1,2})'
            )
            ->setParameter(
                'html_format_regexp',
                "<b>Estimated GT Date</b>[[:space:]]*from[[:space:]]*'0000-00-00'[[:space:]]*to[[:space:]]*'[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}'"
            )
            ->orderBy('ml.parent_id', 'ASC')
            ->addOrderBy('ml.id', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    public function getModLogs(int|array $parentIds, string $module)
    {
        if (\is_int($parentIds)) {
            $parentIds = [$parentIds];
        }

        if ([] === $parentIds) {
            return [];
        }

        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $queryBuilder
            ->select('ml.*', 'p.firstname', 'p.lastname')
            ->from('mod_logs', 'ml')
            ->join('ml', 'people', 'p', 'ml.poster = p.id')
            ->where($queryBuilder->expr()->in('ml.parent_id ', ':parentIds'))
            ->andWhere('ml.module = :module')
            ->andWhere('ml.log_num = 0')
            ->setParameter('parentIds', $parentIds, ArrayParameterType::INTEGER)
            ->setParameter('module', $module)
        ;

        $result = $this->legacyConnection->executeQuery($queryBuilder->getSQL(), $queryBuilder->getParameters(), $queryBuilder->getParameterTypes());

        return $result->fetchAllAssociative();
    }
}
