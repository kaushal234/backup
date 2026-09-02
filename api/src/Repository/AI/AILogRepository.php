<?php

declare(strict_types=1);

namespace App\Repository\AI;

use App\AI\Service\Summarizer\AILogSummarizer;
use App\Entity\AI\AILog;
use App\Entity\AI\Request;
use App\Entity\Directory\People;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AILogRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly AILogSummarizer $summarizer,
    ) {
        parent::__construct($registry, AILog::class);
    }

    public function countPinnedByPeople(People $people, ?int $excludeId = null): int
    {
        $qb = $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.people = :people')
            ->andWhere('l.pinned = true')
            ->setParameter('people', $people);

        if (null !== $excludeId) {
            $qb->andWhere('l.id != :excludeId')
                ->setParameter('excludeId', $excludeId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Returns the file paths of all AIFiles attached to a People's logs.
     *
     * @return string[]
     */
    public function findFilePathsByPeople(People $people): array
    {
        return array_column(
            $this->createQueryBuilder('l')
                ->select('f.filePath')
                ->join('l.requests', 'r')
                ->join('r.file', 'f')
                ->where('l.people = :people')
                ->setParameter('people', $people)
                ->getQuery()
                ->getArrayResult(),
            'filePath'
        );
    }

    public function deleteAllForPeople(People $people): void
    {
        $this->createQueryBuilder('l')
            ->delete()
            ->where('l.people = :people')
            ->setParameter('people', $people)
            ->getQuery()
            ->execute();
    }

    /**
     * Returns AILog IDs older than 30 days for a given people.
     *
     * @return int[]
     */
    public function findIdsOlderThan30DaysForPeople(int $peopleId): array
    {
        $threshold = new \DateTimeImmutable('-30 days');

        return array_column(
            $this->createQueryBuilder('l')
                ->select('l.id')
                ->where('l.people = :people')
                ->andWhere('l.createdAt < :threshold')
                ->andWhere('l.pinned = false')
                ->setParameter('people', $peopleId)
                ->setParameter('threshold', $threshold)
                ->getQuery()
                ->getArrayResult(),
            'id'
        );
    }

    /**
     * Returns AILog IDs beyond the $limit most recent ones for a given people.
     * The limit applies to all logs (pinned or not), but pinned logs are never returned
     * as candidates for deletion — only non-pinned excess logs are returned.
     *
     * @return int[]
     */
    public function findIdsExceedingLimitForPeople(int $peopleId, int $limit = 30): array
    {
        $rows = $this->createQueryBuilder('l')
            ->select('l.id', 'l.pinned')
            ->where('l.people = :people')
            ->orderBy('l.id', 'DESC')
            ->setParameter('people', $peopleId)
            ->getQuery()
            ->getArrayResult();

        $excess = \array_slice($rows, $limit);

        return array_column(
            array_filter($excess, static fn (array $row) => !$row['pinned']),
            'id'
        );
    }

    public function backfillLastResponseContent(AILog $log, string $content): void
    {
        if ('' === $content) {
            return;
        }

        $last = $log->getRequests()->last();
        if (false === $last || null === $last->response || !empty($last->response->content)) {
            return;
        }

        $last->response->content = $content;
        $this->getEntityManager()->flush();
    }

    public function addTitle(AILog $log): void
    {
        $request = $log->getRequests()->first();
        $transcript = $this->buildTranscript([$request]);

        $title = $this->summarizer->summarizeRequest($transcript);

        $log->title = $title;

        $this->getEntityManager()->flush();
    }

    /**
     * Summarizes older requests from the conversation log in chunks.
     *
     * The goal of this method is to progressively summarize past requests in order to
     * keep a compact conversation history while preserving the most recent messages.
     *
     * How it works:
     * 1. Keeps the latest `$tailSize` requests untouched (they remain fully detailed).
     * 2. Processes older requests in chunks of `$chunkSize`.
     * 3. Uses `$log->summarizedRequestsCount` to know how many requests were already summarized.
     * 4. Builds a transcript from the next chunk of requests to summarize.
     * 5. Sends that transcript to the summarizer service along with the existing summary.
     * 6. Updates the global conversation summary with the new summarized result.
     * 7. Persists the updated summary and summarized request counter.
     *
     * This allows the conversation history to grow indefinitely while keeping the
     * prompt size manageable for AI processing.
     */
    public function summarizePreviousRequests(AILog $log, int $tailSize = 4, int $chunkSize = 6): AILog
    {
        $totalRequests = $log->getRequests()->count();
        $alreadySummarizedCount = $log->summarizedRequestsCount;

        $summarizableRequestsCount = $totalRequests - $tailSize;
        $remainingRequestsToSummarize = $summarizableRequestsCount - $alreadySummarizedCount;

        // Not enough requests to summarize anything yet:
        // - we must keep the last $tailSize requests untouched
        // - and only summarize when at least one full chunk is available
        if ($totalRequests < ($tailSize + $chunkSize) || $remainingRequestsToSummarize < $chunkSize) {
            return $log;
        }

        /** @var Request[] $requestsToSummarize */
        $requestsToSummarize = $log->getRequests()->slice($alreadySummarizedCount, $chunkSize);

        if ([] === $requestsToSummarize) {
            return $log;
        }

        $transcript = $this->buildTranscript($requestsToSummarize);

        $log->summarizedRequestsCount = $alreadySummarizedCount + \count($requestsToSummarize);

        if (\count($transcript) > 0) {
            $log->conversationSummary = $this->summarizer->summarizeConversation($transcript, $log->conversationSummary);
        }

        $this->getEntityManager()->flush();

        return $log;
    }

    /**
     * @param Request[] $requests
     */
    private function buildTranscript(array $requests): array
    {
        $transcript = [];
        foreach ($requests as $request) {
            if (null !== $request->content) {
                $transcript[] = \sprintf("[user]\n%s", $request->content);

                if (null !== $request->response) {
                    $transcript[] = \sprintf("[assistant]\n%s", $request->response->content);
                }
            }
        }

        return $transcript;
    }
}
