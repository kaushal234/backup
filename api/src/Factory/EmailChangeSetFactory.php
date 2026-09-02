<?php

declare(strict_types=1);

namespace App\Factory;

use Doctrine\ORM\EntityManagerInterface;

class EmailChangeSetFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function createChangeSetForEmail(object $object, array $unsetKeys = [], string $dateFormat = 'm-Y'): array
    {
        $uow = $this->entityManager->getUnitOfWork();
        $uow->computeChangeSets();

        /** @var array<string, array{0: mixed, 1: mixed}> $changeSet */
        $changeSet = $uow->getEntityChangeSet($object);

        foreach ($uow->getScheduledCollectionUpdates() as $collection) {
            if ($collection->getOwner() !== $object) {
                continue;
            }
            $deleteDiff = $collection->getDeleteDiff();
            $insertDiff = $collection->getInsertDiff();
            if ([] === $deleteDiff && [] === $insertDiff) {
                continue;
            }
            $changeSet[$collection->getMapping()->fieldName] = [$deleteDiff, $insertDiff];
        }

        foreach ($unsetKeys as $key) {
            unset($changeSet[$key]);
        }

        foreach ($changeSet as $field => $entry) {
            [$before, $after] = $entry;

            if (\is_array($before) || \is_array($after)) {
                $changeSet[$field] = [
                    implode(', ', array_map('strval', (array) $before)),
                    implode(', ', array_map('strval', (array) $after)),
                ];
                continue;
            }

            if ($before instanceof \DateTimeInterface && $after instanceof \DateTimeInterface) {
                if (($before = $before->format($dateFormat)) === ($after = $after->format($dateFormat))) {
                    unset($changeSet[$field]);
                    continue;
                }
                $changeSet[$field] = [$before, $after];
                continue;
            }

            $before = $before instanceof \DateTimeInterface ? $before->format('Y-m-d') : $before;
            $after = $after instanceof \DateTimeInterface ? $after->format('Y-m-d') : $after;

            $changeSet[$field] = [(string) $before, (string) $after];
        }

        return $changeSet;
    }
}
