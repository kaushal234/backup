<?php

declare(strict_types=1);

namespace App\AI\Service\Loader\Comment;

use App\AI\Dto\Activity\CommentModel;
use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use Doctrine\DBAL\Connection;

class LegacyCommentLoader
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly PeopleRepository $peopleRepository,
    ) {
    }

    /**
     * @return CommentModel[]
     */
    public function findComments(object $entity, string $module): array
    {
        if (!method_exists($entity, 'getId')) {
            throw new \LogicException(\sprintf('Entity "%s" must expose a getId() method to be used as a legacy comment parent.', $entity::class));
        }

        /** @var int|null $parentId */
        $parentId = $entity->getId();
        if (null === $parentId) {
            return [];
        }

        $rows = $this->legacyConnection->createQueryBuilder()
            ->select('comment', 'date', 'poster')
            ->from('mod_logs')
            ->where('module = :module')
            ->andWhere('parent_id = :parent_id')
            ->orderBy('date', 'ASC')
            ->setParameters([
                'module' => $module,
                'parent_id' => $parentId,
            ])
            ->executeQuery()
            ->fetchAllAssociative();

        $authors = $this->loadAuthors($rows);

        $comments = [];
        foreach ($rows as $row) {
            $message = mb_trim((string) ($row['comment'] ?? ''));
            if ('' === $message) {
                continue;
            }

            $author = isset($row['poster']) ? ($authors[(int) $row['poster']] ?? null) : null;
            $comments[] = new CommentModel(
                message: $message,
                createdAt: new \DateTimeImmutable((string) $row['date']),
                authorEmail: $author?->getEmail(),
                authorFirstname: $author?->getFirstname(),
                authorLastname: $author?->getLastname(),
            );
        }

        return $comments;
    }

    /**
     * @param list<array<string, mixed>> $rows
     *
     * @return array<int, People>
     */
    private function loadAuthors(array $rows): array
    {
        $legacyIds = [];
        foreach ($rows as $row) {
            if (isset($row['poster']) && '' !== $row['poster']) {
                $legacyIds[(int) $row['poster']] = true;
            }
        }

        if ([] === $legacyIds) {
            return [];
        }

        $authors = [];
        /** @var People $people */
        foreach ($this->peopleRepository->findBy(['legacyId' => array_keys($legacyIds)]) as $people) {
            $legacyId = $people->getLegacyId();
            if (null !== $legacyId) {
                $authors[$legacyId] = $people;
            }
        }

        return $authors;
    }
}
