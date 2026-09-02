<?php

declare(strict_types=1);

namespace App\Tests\ExternalERP\Filter;

use ApiPlatform\Metadata\FilterInterface;
use App\ExternalERP\Filter\ContainsFilter;
use App\ExternalERP\Filter\EqualsFilter;
use PHPUnit\Framework\TestCase;

abstract class AbstractFilterTest extends TestCase
{
    public function testGetPropertiesReturnsSameArray(): void
    {
        $properties = ['foo' => null, 'bar' => null];
        $filterClass = $this->getFilterClass();
        /** @var ContainsFilter|EqualsFilter $filter */
        $filter = new $filterClass($properties);

        self::assertSame($properties, $filter->getProperties());
    }

    public function testGetDescriptionBuildsCorrectly(): void
    {
        $properties = ['foo' => null, 'bar' => null];
        $filterClass = $this->getFilterClass();
        $filter = new $filterClass($properties);

        $description = $filter->getDescription(\stdClass::class);

        $expected = [];
        foreach ($properties as $property => $_) {
            $expected[\sprintf('%s[%s]', $this->getFilterConstant(), $property)] = [
                'property' => $property,
                'type' => 'string',
                'is_collection' => true,
                'description' => $this->getDescriptionText(),
                'required' => false,
            ];
        }

        self::assertSame($expected, $description);
    }

    public function testEmptyPropertiesReturnsEmptyDescription(): void
    {
        $filterClass = $this->getFilterClass();
        $filter = new $filterClass([]);

        self::assertSame([], $filter->getDescription(\stdClass::class));
    }

    /**
     * @return class-string<FilterInterface>
     */
    abstract protected function getFilterClass(): string;

    abstract protected function getFilterConstant(): string;

    abstract protected function getDescriptionText(): string;
}
