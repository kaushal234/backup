<?php

declare(strict_types=1);

namespace AppBundle\DataTable\Filter\Type\Mis;

use AppBundle\DataTable\Type\Mis\GuestUser\GuestUserDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;

class GuestUserDataTableTypeTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideUpdateTasksRatioCases
     */
    public function testUpdateTasksRatioFormatter(?array $updateTasks, string $expected): void
    {
        $formatter = $this->extractUpdateTasksRatioFormatter();

        $result = $formatter($updateTasks);

        $this->assertSame($expected, $result);
    }

    public static function provideUpdateTasksRatioCases(): iterable
    {
        yield 'null tasks returns 0/0' => [
            null,
            '0/0',
        ];

        yield 'empty array returns 0/0' => [
            [],
            '0/0',
        ];

        yield 'all tasks done (array format)' => [
            [
                ['done' => true],
                ['done' => true],
            ],
            '0/2 (0%)',
        ];

        yield 'all tasks open (array format)' => [
            [
                ['done' => false],
                ['done' => false],
            ],
            '2/2 (100%)',
        ];

        yield 'mixed tasks, percentage rounded (array format)' => [
            [
                ['done' => true],
                ['done' => false],
                ['done' => false],
            ],
            '2/3 (67%)',
        ];

        yield 'task without "done" key defaults to done (array format)' => [
            [
                [],
                ['done' => false],
            ],
            '1/2 (50%)',
        ];

        yield 'open task as object (object format)' => [
            [
                (object) ['done' => false],
                (object) ['done' => true],
            ],
            '1/2 (50%)',
        ];

        yield 'task without "done" property defaults to done (object format)' => [
            [
                (object) [],
                (object) ['done' => false],
            ],
            '1/2 (50%)',
        ];
    }

    /**
     * Builds a GuestUserDataTableType, calls buildDataTable() against a
     * stubbed DataTableBuilderInterface, and returns the 'formatter'
     * closure registered for the 'updateTasksRatio' column so it can be
     * tested in isolation.
     */
    private function extractUpdateTasksRatioFormatter(): \Closure
    {
        $formatter = null;

        $builderProphecy = $this->prophesize(DataTableBuilderInterface::class);

        $revealedBuilder = $builderProphecy->reveal();

        $builderProphecy
            ->addColumn(Argument::any(), Argument::any(), Argument::any())
            ->will(static function (array $args) use (&$formatter, $revealedBuilder) {
                if ('updateTasksRatio' === $args[0]) {
                    $formatter = $args[2]['formatter'];
                }

                return $revealedBuilder;
            });

        $builderProphecy->addFilter(Argument::cetera())->willReturn($revealedBuilder);
        $builderProphecy->setSearchHandler(Argument::cetera())->willReturn($revealedBuilder);
        $builderProphecy->setDefaultSortingData(Argument::cetera())->willReturn($revealedBuilder);
        $builderProphecy->addRowAction(Argument::cetera())->willReturn($revealedBuilder);
        $builderProphecy->setDefaultExportData(Argument::cetera())->willReturn($revealedBuilder);
        $builderProphecy->addExporter(Argument::cetera())->willReturn($revealedBuilder);

        $type = new GuestUserDataTableType();
        $type->buildDataTable($revealedBuilder, []);

        $this->assertInstanceOf(
            \Closure::class,
            $formatter,
            'The "updateTasksRatio" column formatter was not registered.'
        );

        return $formatter;
    }
}
