<?php

declare(strict_types=1);

namespace App\Tests\Report;

use App\Report\ReportQueriesBuilder;
use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class ReportQueriesBuilderTest extends TestCase
{
    use ProphecyTrait;

    public function testValueSelectPartIsCorrectlyReplaced()
    {
        $queryBuilderMock = $this->createMock(QueryBuilder::class);

        $selectValuePart = new Select('COUNT(o) AS value');
        $otherParts = [
            new Select('anything AS another'),
            new Select('nothing AS other'),
        ];

        $queryBuilderMock->expects($this->once())->method('getDQLPart')->with('select')->willReturn([$selectValuePart, ...$otherParts]);
        $queryBuilderMock->expects($this->once())->method('resetDQLPart')->with('select');

        $queryBuilderMock->expects($this->exactly(3))->method('addSelect')
            ->withConsecutive(
                ['replacement AS value'],
                [['anything AS another']],
                [['nothing AS other']],
            );

        ReportQueriesBuilder::replaceSelectValuePart($queryBuilderMock, 'replacement AS value');
    }
}
