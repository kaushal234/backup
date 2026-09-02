<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use Behat\Behat\Context\Context;

class DatabaseContext implements Context
{
    use AssertionTrait;
    use DoctrineAwareTrait;
    use MinkAwareTrait;

    /**
     * @Then less than :number database queries must have been executed
     * @Then less than :number database query must have been executed
     */
    public function lessThanDatabaseQueriesMustHaveBeenExecuted(int $number)
    {
        $this->assert(
            $number > $result = $this->getDoctrineDataCollector()->getQueryCount(),
            \sprintf('The query count should be less than %d but there was %d queries', $number, $result)
        );
    }

    /**
     * @Then :number database queries must have been executed
     * @Then :number database query must have been executed
     */
    public function exactQueriesMustHaveBeenExcecuted(int $number)
    {
        $this->assert(
            $number === $result = (int) $this->getDoctrineDataCollector()->getQueryCount(),
            \sprintf('The query count should be %d but there was %d queries', $number, $result)
        );
    }

    /**
     * @Then :number database queries must have been executed against :connection connection
     * @Then :number database query must have been executed against :connection connection
     */
    public function exactQueriesMustHaveBeenExcecutedAgainstConnection(int $number, string $connection)
    {
        $dataCollector = $this->getDoctrineDataCollector();
        $this->assertCount($number, $dataCollector->getQueries()[$connection]);
    }

    /**
     * @Then I reset the query count
     */
    public function iResetTheQueryCount()
    {
        $this->getDoctrineDataCollector()->reset();
    }

    /**
     * @Then an insert query has been executed on the table :table
     * @Then :expectedCount insert queries has been executed on the table :table
     */
    public function insertQueriesHasBeenInsertedOnTheTable(string $table, int $expectedCount = 1)
    {
        $insertQueriesCount = $this->findMatchingQueriesCount(\sprintf('/INSERT.+%s /', $table));
        $this->assertSame($expectedCount, $insertQueriesCount,
            \sprintf('%d "insert" queries found, but should be %d.', $insertQueriesCount, $expectedCount)
        );
    }
}
