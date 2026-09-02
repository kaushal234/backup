<?php

declare(strict_types=1);

namespace LegacyBundle\Manager;

use Doctrine\DBAL\Connection;

class ModLinkManager
{
    private readonly Connection $legacyConnection;

    public function __construct(Connection $legacyConnection)
    {
        $this->legacyConnection = $legacyConnection;
    }

    public function createLink(int $parentId, string $module, int $item, string $type): void
    {
        $qb = $this->legacyConnection->createQueryBuilder();
        $qb
            ->insert('mod_links')
            ->setValue('parent_id', ':parent_id')
            ->setValue('module', ':module')
            ->setValue('item', ':item')
            ->setValue('type', ':type')
            ->setParameters([
                'parent_id' => $parentId,
                'module' => $module,
                'item' => $item,
                'type' => $type,
            ])
        ;

        $stmt = $this->legacyConnection->prepare($qb->getSQL());
        foreach ($qb->getParameters() as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->executeQuery();
    }

    public function getLinks(
        ?string $module = null,
        ?int $moduleId = null,
        ?string $type = null,
        ?int $typeId = null
    ) {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $queryBuilder
            ->select('ml.*')
            ->from('mod_links', 'ml')
        ;

        if ($module) {
            $queryBuilder
                ->andWhere('ml.module = :module')
                ->setParameter('module', $module)
            ;
        }

        if ($moduleId) {
            $queryBuilder
                ->andWhere('ml.parent_id = :moduleId')
                ->setParameter('moduleId', $moduleId)
            ;
        }

        if ($type) {
            $queryBuilder
                ->andWhere('ml.type = :type')
                ->setParameter('type', $type)
            ;
        }

        if ($typeId) {
            $queryBuilder
                ->andWhere('ml.item = :typeId')
                ->setParameter('typeId', $typeId)
            ;
        }

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }

    public function getFromToLinks(int $id, string $module): array
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $queryBuilder
            ->select('ml.*')
            ->from('mod_links', 'ml')
            ->where('(ml.module = :module AND ml.parent_id = :id)')
            ->orWhere('(ml.type = :module AND ml.item = :id)')
            ->setParameter('module', $module)
            ->setParameter('id', $id)
        ;

        return $queryBuilder->executeQuery()->fetchAllAssociative();
    }
}
