<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use App\Tests\Behat\Context\Legacy\Helpers\QueryDecorator;
use Symfony\Bridge\Doctrine\DataCollector\DoctrineDataCollector;

trait DoctrineAwareTrait
{
    use MinkAwareTrait;

    private function getDoctrineDataCollector(): DoctrineDataCollector
    {
        $collector = $this->getClientSymfonyProfile()->getCollector('db');

        if (!$collector instanceof DoctrineDataCollector) {
            throw new \RuntimeException(\sprintf('Invalid DataCollector class, expecting %s, %s given', DoctrineDataCollector::class, $collector::class));
        }

        return $collector;
    }

    private function findMatchingQuery(string $pattern, $connection = 'default'): bool
    {
        foreach ($this->getDoctrineQueries($connection) as $query) {
            if (preg_match($pattern, (string) $query)) {
                return true;
            }
        }

        return false;
    }

    private function findMatchingQueriesCount(string $pattern, $connection = 'default'): int
    {
        $count = 0;
        foreach ($this->getDoctrineQueries($connection) as $query) {
            if (preg_match($pattern, (string) $query)) {
                ++$count;
            }
        }

        return $count;
    }

    private function getDoctrineQueries($connection = 'default'): array
    {
        $collector = $this->getDoctrineDataCollector();

        return array_map(
            static fn (array $preparedQuery) => QueryDecorator::replaceQueryParameters($preparedQuery['sql'], $preparedQuery['params']),
            $collector->getQueries()[$connection] ?? []
        );
    }

    private function assertUpdateQuery(string $column, string $table, string $condition, string $connection = 'default')
    {
        $updateQueryFound = $this->findMatchingQuery(\sprintf('/UPDATE\s*%s.+%s\s*%s/s', $table, $column, $condition), $connection);
        $this->assertTrue($updateQueryFound, \sprintf('No update event for %s on %s (with "%s")', $column, $table, $condition));
    }

    private function assertNoUpdateQuery(string $column, string $table, string $connection = 'default')
    {
        $updateQueryFound = $this->findMatchingQuery(\sprintf('/UPDATE\s*%s.+%s\s*/', $table, $column), $connection);
        $this->assertFalse($updateQueryFound, \sprintf('there was an update event for %s on %s', $column, $table));
    }

    private function assertInsertQuery(string $column, string $table, string $condition, string $connection = 'default')
    {
        $insertQueryFound = $this->findMatchingQuery(\sprintf('/INSERT INTO\s*%s.+%s\s*%s/s', $table, $column, $condition), $connection);
        $this->assertTrue($insertQueryFound, \sprintf('No insert event for %s on %s (with "%s")', $column, $table, $condition));
    }

    private function assertNotInsertQuery(string $table, string $connection = 'default')
    {
        $insertQueryFound = $this->findMatchingQuery(\sprintf('/INSERT INTO\s*%s.+\s*/s', $table), $connection);
        $this->assertFalse($insertQueryFound, \sprintf('Insert event for table %s', $table));
    }
}
