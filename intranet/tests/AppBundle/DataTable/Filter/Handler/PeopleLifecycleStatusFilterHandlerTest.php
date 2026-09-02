<?php

declare(strict_types=1);

namespace App\Tests\AppBundle\DataTable\Filter\Handler;

use AppBundle\DataTable\Filter\Handler\LeaverStatusFilterHandler;
use AppBundle\DataTable\Filter\Handler\NewComerStatusFilterHandler;
use AppBundle\DataTable\Query\ApiProxyQuery;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ArrayProxyQuery;
use PHPUnit\Framework\TestCase;

class PeopleLifecycleStatusFilterHandlerTest extends TestCase
{
    public function testLeaverThrowsOnUnexpectedQueryType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        (new LeaverStatusFilterHandler())->handle(
            new ArrayProxyQuery([]),
            new FilterData(),
            $this->createStub(FilterInterface::class)
        );
    }

    /**
     * @dataProvider singleStatusProvider
     */
    public function testSingleStatusAppliesDisabledFilter(object $handler, mixed $value, string $expected): void
    {
        $query = $this->createMock(ApiProxyQuery::class);
        $query->expects($this->once())->method('setFilter')->with('disabled', $expected);

        $handler->handle($query, FilterData::fromArray(['value' => $value]), $this->createStub(FilterInterface::class));
    }

    /**
     * @dataProvider noFilterProvider
     */
    public function testNoneOrAllStatusesAppliesNoFilter(object $handler, mixed $value): void
    {
        $query = $this->createMock(ApiProxyQuery::class);
        $query->expects($this->never())->method('setFilter');

        $handler->handle($query, FilterData::fromArray(['value' => $value]), $this->createStub(FilterInterface::class));
    }

    public static function singleStatusProvider(): array
    {
        $leaver = new LeaverStatusFilterHandler();
        $newComer = new NewComerStatusFilterHandler();

        return [
            'leaver planned => still enabled' => [$leaver, [LeaverStatusFilterHandler::STATUS_PLANNED], 'false'],
            'leaver left => disabled' => [$leaver, [LeaverStatusFilterHandler::STATUS_LEFT], 'true'],
            'new comer planned => not yet enabled' => [$newComer, [NewComerStatusFilterHandler::STATUS_PLANNED], 'true'],
            'new comer arrived => enabled' => [$newComer, [NewComerStatusFilterHandler::STATUS_ARRIVED], 'false'],
        ];
    }

    public static function noFilterProvider(): array
    {
        $leaver = new LeaverStatusFilterHandler();
        $newComer = new NewComerStatusFilterHandler();

        return [
            'leaver none' => [$leaver, []],
            'leaver both' => [$leaver, [LeaverStatusFilterHandler::STATUS_PLANNED, LeaverStatusFilterHandler::STATUS_LEFT]],
            'new comer none' => [$newComer, []],
            'new comer both' => [$newComer, [NewComerStatusFilterHandler::STATUS_PLANNED, NewComerStatusFilterHandler::STATUS_ARRIVED]],
        ];
    }
}
