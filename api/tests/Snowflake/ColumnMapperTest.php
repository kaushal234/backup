<?php

declare(strict_types=1);

namespace App\Tests\Snowflake;

use App\Snowflake\ColumnMapper;
use App\Tests\Snowflake\Fixtures\DummyResource;
use PHPUnit\Framework\TestCase;

class ColumnMapperTest extends TestCase
{
    public function testGetColumnsReturnsColumnsInDeclarationOrderAndSkipsUnmappedProperties(): void
    {
        self::assertSame(
            ['site' => 'c.SITE', 'item' => 'c.ITEM', 'description' => 'i.DSCA'],
            ColumnMapper::getColumns(DummyResource::class),
        );
    }

    public function testGetColumnReturnsMappedColumn(): void
    {
        self::assertSame('c.SITE', ColumnMapper::getColumn(DummyResource::class, 'site'));
        self::assertSame('i.DSCA', ColumnMapper::getColumn(DummyResource::class, 'description'));
    }

    public function testGetColumnThrowsForUnmappedProperty(): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('Property "untracked" of "'.DummyResource::class.'" has no #[Column] attribute.');

        ColumnMapper::getColumn(DummyResource::class, 'untracked');
    }

    public function testGetColumnThrowsForUnknownProperty(): void
    {
        self::expectException(\LogicException::class);

        ColumnMapper::getColumn(DummyResource::class, 'doesNotExist');
    }

    public function testGetColumnsIsStableAcrossCalls(): void
    {
        // exercises the static cache branch: a second call must return the same mapping.
        $first = ColumnMapper::getColumns(DummyResource::class);
        $second = ColumnMapper::getColumns(DummyResource::class);

        self::assertSame($first, $second);
    }
}
