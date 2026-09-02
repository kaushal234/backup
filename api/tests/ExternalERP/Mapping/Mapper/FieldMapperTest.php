<?php

declare(strict_types=1);

namespace App\Tests\ExternalERP\Mapping\Mapper;

use App\ExternalERP\Mapping\Attribute\ERPField;
use App\ExternalERP\Mapping\Mapper\FieldMapper;
use PHPUnit\Framework\TestCase;

final class FieldMapperTest extends TestCase
{
    public function testMappingWithoutERPField(): void
    {
        $mapper = new FieldMapper();

        $class = new class {
            public string $foo;
        };

        $expected = [
            'foo' => 'foo',
        ];

        self::assertSame($expected, $mapper->getMapping($class::class));
    }

    public function testMappingWithERPField(): void
    {
        $mapper = new FieldMapper();

        $class = new class {
            #[ERPField(name: 'erp_bar')]
            public string $bar;
        };

        $expected = [
            'bar' => 'erp_bar',
        ];

        self::assertSame($expected, $mapper->getMapping($class::class));
    }

    public function testMappingMixedProperties(): void
    {
        $mapper = new FieldMapper();

        $class = new class {
            public string $foo;

            #[ERPField(name: 'erp_bar')]
            public string $bar;

            public string $baz;
        };

        $expected = [
            'foo' => 'foo',
            'bar' => 'erp_bar',
            'baz' => 'baz',
        ];

        self::assertSame($expected, $mapper->getMapping($class::class));
    }
}
