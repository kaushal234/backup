<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context\Legacy;

use App\Tests\Behat\Context\AssertionTrait;
use App\Tests\Behat\Context\DoctrineAwareTrait;
use App\Tests\Behat\Context\MinkAwareTrait;
use Behat\Behat\Context\Context;

class LegacyContext implements Context
{
    use AssertionTrait;
    use DoctrineAwareTrait;
    use MinkAwareTrait;

    /**
     * @Then a new row has been inserted in the legacy table :table
     * @Then :expectedCount new rows have been inserted in the legacy table :table
     * @Then an insert query has been executed on the legacy table :table
     * @Then :expectedCount insert queries has been executed on the legacy table :table
     */
    public function aNewRowHasBeenInsertedInTheLegacyTable(string $table, int $expectedCount = 1)
    {
        $insertQueriesCount = $this->findMatchingQueriesCount(\sprintf('/INSERT.+%s /', $table), 'legacy');
        $this->assertSame($expectedCount, $insertQueriesCount,
            \sprintf('%d "insert" queries found, but should be %d.', $insertQueriesCount, $expectedCount)
        );
    }

    /**
     * @Then the column :column from the :table legacy table has been inserted with string( matching) :value
     */
    public function theColumnFromTheLegacyTableHasBeenInsertedWithString(string $column, string $table, string $value)
    {
        $this->assertInsertQuery($column, $table, \sprintf("= '%s'", $value), 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has been inserted with a string containing today's date
     */
    public function theColumnFromTheLegacyTableHasBeenInsertedWithTodaysDate(string $column, string $table)
    {
        $this->assertInsertQuery($column, $table, \sprintf("= '.*%s.*'", date('Y-m-d')), 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has been inserted with unquoted string :value
     */
    public function theColumnFromTheLegacyTableHasBeenInsertedWithUnquotedString(string $column, string $table, string $value)
    {
        $this->assertInsertQuery($column, $table, \sprintf('= %s', $value), 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has been inserted with integer :value
     */
    public function theColumnFromTheLegacyTableHasBeenInsertedWithInteger(string $column, string $table, int $value)
    {
        $this->assertInsertQuery($column, $table, \sprintf('= %d', $value), 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has been inserted with a string containing :value
     */
    public function theColumnFromTheLegacyTableHasBeenInsertedWithStringContaining(string $column, string $table, string $value)
    {
        $this->assertInsertQuery($column, $table, \sprintf("= '.*%s.*'", $value), 'legacy');
    }

    /**
     * @Then table :table has not been inserted
     */
    public function tableHasNotBeenInserted(string $table)
    {
        $this->assertNotInsertQuery($table, 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has been inserted
     */
    public function theColumnFromTheLegacyTableHasBeenInserted(string $column, string $table)
    {
        $this->assertInsertQuery($column, $table, '', 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has been updated
     */
    public function theColumnFromTheLegacyTableHasBeenUpdated(string $column, string $table)
    {
        $this->assertUpdateQuery($column, $table, '', 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has not been updated
     */
    public function theColumnFromTheLegacyTableHasNotBeenUpdated(string $column, string $table)
    {
        $this->assertNoUpdateQuery($column, $table, 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has been updated with integer :value
     */
    public function theColumnFromTheLegacyTableHasBeenUpdatedWithInteger(string $column, string $table, int $value)
    {
        $this->assertUpdateQuery($column, $table, \sprintf('= %d', $value), 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has been updated with number :value
     */
    public function theColumnFromTheLegacyTableHasBeenUpdatedWithNumber(string $column, string $table, string $value)
    {
        $this->assertUpdateQuery($column, $table, \sprintf('= %s', $value), 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has been updated with string :value
     */
    public function theColumnFromTheLegacyTableHasBeenUpdatedWithString(string $column, string $table, string $value)
    {
        $value = preg_quote($value, '/');
        $this->assertUpdateQuery($column, $table, \sprintf("= '%s'", $value), 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has been updated with filename :value
     */
    public function theColumnFromTheLegacyTableHasBeenUpdatedWithfilename(string $column, string $table, string $value)
    {
        $splittedName = explode('/', $value);
        $value = \sprintf('%s/%s/%s', $splittedName[0], (new \DateTime())->format('Y/m'), $splittedName[1]);
        $value = preg_quote($value, '/');
        $this->assertUpdateQuery($column, $table, \sprintf("= '%s'", $value), 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has been updated with unquoted string :value
     */
    public function theColumnFromTheLegacyTableHasBeenUpdatedWithUnquotedString(string $column, string $table, string $value)
    {
        $this->assertUpdateQuery($column, $table, \sprintf('= %s', $value), 'legacy');
    }

    /**
     * @Then the column :column from the :table legacy table has been updated with a string containing :value
     */
    public function theColumnFromTheLegacyTableHasBeenUpdatedWithStringContaining(string $column, string $table, string $value)
    {
        $this->assertUpdateQuery($column, $table, \sprintf("= '.*%s.*'", $value), 'legacy');
    }

    /**
     * @Then a row has been deleted in the legacy table :table
     * @Then :count rows have been deleted in the legacy table :table
     * @Then :count row have been deleted in the legacy table :table
     */
    public function newRowsHasBeenDeletedInTheLegacyTable(string $table, int $count = 1)
    {
        $deleteQueryFound = $this->findMatchingQueriesCount(\sprintf('/DELETE.+%s /', $table), 'legacy');
        $this->assertSame($count, $deleteQueryFound, \sprintf('No delete event for %s', $table));
    }

    /**
     * @Then a row where :column with value :value has been deleted from :table legacy table
     */
    public function aRowWhereWithValueHasBeenDeletedFromLegacyTable(string $column, $value, string $table)
    {
        if (\is_int($value)) {
            $deleteQueryFound = $this->findMatchingQuery(\sprintf('/DELETE.+%s.+%s = %d/', $table, $column, $value), 'legacy');
        } elseif (\is_string($value)) {
            $deleteQueryFound = $this->findMatchingQuery(\sprintf("/DELETE.+%s.+%s = '%s'/", $table, $column, $value), 'legacy');
        } elseif (\is_bool($value)) {
            $value = $value ? '1' : '0';
            $deleteQueryFound = $this->findMatchingQuery(\sprintf('/DELETE.+%s.+%s = %s/', $table, $column, $value), 'legacy');
        } else {
            throw new \InvalidArgumentException(\sprintf('The input type %s is not supported yet.', \gettype($value)));
        }
        $this->assertTrue($deleteQueryFound, \sprintf('No delete event for %s', $table));
    }

    /**
     * @Then a row with :column = :value should not exist in the legacy table :table
     */
    public function aRowWithColumnAndValueShouldNotExistInTheLegacyTable(string $column, $value, string $table)
    {
        $connection = $this->getClientContainer()->get('doctrine.dbal.legacy_connection');
        $qb = $connection->createQueryBuilder();
        $qb->select('count(*)')
            ->from($table)
            ->where($qb->expr()->eq($column, ':value'))
            ->setParameter('value', $value);

        $count = (int) $qb->executeQuery()->fetchOne();

        $this->assertSame(0, $count, \sprintf('A row still exists in table %s with %s = %s', $table, $column, $value));
    }
}
