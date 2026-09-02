<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use App\Entity\Service\TechnicianOnCall;
use Doctrine\DBAL\Connection;

class TOCManager
{
    private readonly Connection $legacyConnection;

    public function __construct(Connection $legacyConnection)
    {
        $this->legacyConnection = $legacyConnection;
    }

    public function findPublicFilesofTOCByWCId(int $warrantyClaimId): array
    {
        // by parent
        $WHERE = \sprintf('parent_id = %s', $warrantyClaimId);
        $query = <<<EOF
            		SELECT *
            		FROM mod_links
            		WHERE
            		    $WHERE
            		    AND module='WC'
            		    AND type='TOC'
            		ORDER BY id DESC
            EOF;
        $byParentStmt = $this->legacyConnection->prepare($query);

        // by item
        $WHERE = \sprintf('item = %s', $warrantyClaimId);
        $query = <<<EOF
            		SELECT *
            		FROM mod_links
            		WHERE
            		$WHERE
                        AND module='TOC'
            		    AND type='WC'
            		ORDER BY id DESC
            EOF;
        $byItemStmt = $this->legacyConnection->prepare($query);

        $results = array_merge($byParentStmt->executeQuery()->fetchAllAssociative(), $byItemStmt->executeQuery()->fetchAllAssociative());

        if ([] === $results) {
            return [];
        }

        $tocs = [];
        foreach ($results as $result) {
            $tocs[] = TechnicianOnCall::MODULE_NAME === $result['type'] ? $result['item'] : $result['parent_id'];
        }

        $qb = $this->legacyConnection->createQueryBuilder();
        $qb
            ->select('m.id')
            ->addSelect('m.date')
            ->addSelect('m.description')
            ->addSelect('m.filename')
            ->addSelect('f.filepath')
            ->from('mod_files', 'm')
            ->leftJoin('m', 'file', 'f', 'm.fid = f.id')
            ->where(\sprintf('m.parent_id IN (%s)', implode(', ', $tocs)))
            ->andWhere("m.module = 'TOC'")
            ->andWhere('m.level > 1')
        ;

        $result = $this->legacyConnection->executeQuery($qb->getSQL(), $qb->getParameters());

        return $result->fetchAllAssociative();
    }
}
