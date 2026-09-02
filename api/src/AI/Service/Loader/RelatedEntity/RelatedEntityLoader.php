<?php

declare(strict_types=1);

namespace App\AI\Service\Loader\RelatedEntity;

use App\AI\Dto\Activity\RelatedEntityModel;
use LegacyBundle\Manager\ModLinkManager;

class RelatedEntityLoader
{
    public function __construct(
        private readonly ModLinkManager $modLinkManager,
    ) {
    }

    /**
     * @param 'id'|'legacy_id' $parentIdStrategy
     *
     * @return RelatedEntityModel[]
     */
    public function findRelatedEntities(object $entity, string $parentIdStrategy, string $module): array
    {
        $parentId = $this->resolveParentId($entity, $parentIdStrategy);
        if (null === $parentId) {
            return [];
        }

        $related = [];
        foreach ($this->modLinkManager->getFromToLinks($parentId, $module) as $row) {
            $rowModule = isset($row['module']) ? (string) $row['module'] : '';
            $rowParentId = isset($row['parent_id']) ? (int) $row['parent_id'] : 0;

            if ($rowModule === $module && $rowParentId === $parentId) {
                $type = isset($row['type']) ? (string) $row['type'] : '';
                $item = isset($row['item']) ? (int) $row['item'] : 0;
            } else {
                $type = $rowModule;
                $item = $rowParentId;
            }

            if ('' === $type) {
                continue;
            }

            $related[] = new RelatedEntityModel(
                type: $type,
                item: $item,
            );
        }

        return $related;
    }

    private function resolveParentId(object $entity, string $strategy): ?int
    {
        $getter = match ($strategy) {
            'id' => 'getId',
            'legacy_id' => 'getLegacyId',
            default => throw new \LogicException(\sprintf('Unknown parent_id strategy "%s".', $strategy)),
        };

        if (!method_exists($entity, $getter)) {
            throw new \LogicException(\sprintf('Entity "%s" does not expose %s().', $entity::class, $getter));
        }

        /* @var int|null */
        return $entity->{$getter}();
    }
}
