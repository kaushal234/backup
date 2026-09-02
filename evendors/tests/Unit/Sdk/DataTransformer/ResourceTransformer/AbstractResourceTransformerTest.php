<?php

declare(strict_types=1);

namespace App\Tests\Unit\Sdk\DataTransformer\ResourceTransformer;

use App\Sdk\DataTransformer\ResourceTransformer\ResourceTransformerInterface;
use App\Sdk\Exception\FailedTransformationException;
use App\Sdk\Resource\ResourceInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @group unit
 *
 * @template T of ResourceInterface
 */
abstract class AbstractResourceTransformerTest extends TestCase
{
    /**
     * @return iterable<array{0: array<string, mixed>}>
     */
    abstract public function getCorrectStructures(): iterable;

    /**
     * @return iterable<array{0: array<string, mixed>}>
     */
    abstract public function getIncorrectStructures(): iterable;

    /**
     * @dataProvider getIncorrectStructures
     */
    public function testDoesNotSupportIncorrectStructures(array $structure): void
    {
        self::assertFalse($this->getResourceTransformer()->supports($this->getResourceClass(), $structure));
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testDoesNotSupportCorrectStructuresWithNonSupportedResource(array $structure): void
    {
        self::assertFalse($this->getResourceTransformer()->supports(stdClass::class, $structure));
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testSupportsCorrectStructures(array $structure): void
    {
        // dump($structure, $this->getResourceTransformer(), $this->getResourceClass());
        // $transformer = $this->getResourceTransformer();
        // $transformer->transform($structure);

        self::assertTrue($this->getResourceTransformer()->supports($this->getResourceClass(), $structure));
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testTransform(array $structure): void
    {
        $resource = $this->getResourceTransformer()->transform($structure);

        $this->assertResourceMatchesStructure($resource, $structure, false);
    }

    /**
     * @dataProvider getIncorrectStructures
     */
    public function testTransformationFails(array $structure): void
    {
        $this->expectException(FailedTransformationException::class);
        $this->expectExceptionMessageMatches('/Failed to transform the given data/');

        $this->getResourceTransformer()->transform($structure);
    }

    /**
     * @dataProvider getIncorrectStructures
     */
    public function testDoesNotSupportIncorrectCollectionStructures(array $structure): void
    {
        if (!$this->supportsCollections()) {
            self::expectNotToPerformAssertions();

            return;
        }

        self::assertFalse($this->getResourceTransformer()->supportsCollection($this->getResourceClass(), [
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
        ]));

        self::assertFalse($this->getResourceTransformer()->supportsCollection($this->getResourceClass(), $structure));
    }

    /**
     * @dataProvider getIncorrectStructures
     */
    public function testDoesNotSupportIncorrectCollectionStructuresWithNonSupportedResource(array $structure): void
    {
        self::assertFalse($this->getResourceTransformer()->supportsCollection(stdClass::class, [
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
        ]));
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testSupportsCorrectCollectionStructures(array $structure): void
    {
        $supported = $this->getResourceTransformer()->supportsCollection($this->getResourceClass(), [
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
        ]);

        if (!$this->supportsCollections()) {
            self::assertFalse($supported);
        } else {
            self::assertTrue($supported);
        }
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testTransformCollection(array $structure): void
    {
        if (!$this->supportsCollections()) {
            self::expectNotToPerformAssertions();

            return;
        }

        $collection = $this->getResourceTransformer()->transformCollection([
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
        ]);

        self::assertCount(1, $collection);

        $resource = $collection->at(0);

        $this->assertResourceMatchesStructure($resource, $structure, true);
    }

    /**
     * @dataProvider getIncorrectStructures
     */
    public function testTransformCollectionFails(array $structure): void
    {
        if (!$this->supportsCollections()) {
            self::expectNotToPerformAssertions();

            return;
        }

        $this->expectException(FailedTransformationException::class);
        $this->expectExceptionMessage('Failed to transform the given data into a list');

        $this->getResourceTransformer()->transformCollection([
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
        ]);
    }

    /**
     * @dataProvider getIncorrectStructures
     */
    public function testDoesNotSupportIncorrectPageStructuresForCollection(array $structure): void
    {
        if (!$this->supportsPage()) {
            self::expectNotToPerformAssertions();

            return;
        }

        self::assertFalse($this->getResourceTransformer()->supportsPage($this->getResourceClass(), [
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
        ]));

        self::assertFalse($this->getResourceTransformer()->supportsCollection($this->getResourceClass(), $structure));
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testDoesNotSupportIncorrectPageStructuresWithNonSupportedResourceForCollection(array $structure): void
    {
        self::assertFalse($this->getResourceTransformer()->supportsPage(stdClass::class, [
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
        ]));
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testSupportsCorrectPageStructures(array $structure): void
    {
        $supported = $this->getResourceTransformer()->supportsPage($this->getResourceClass(), [
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
            'hydra:totalItems' => 1,
            'hydra:view' => [
                '@type' => 'hydra:PartialCollectionView',
            ],
        ]);

        if ($this->supportsPage()) {
            self::assertTrue($supported);
        } else {
            self::assertFalse($supported);
        }
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testSupportsCorrectPageStructuresWithNext(array $structure): void
    {
        if (!$this->supportsPage()) {
            self::expectNotToPerformAssertions();

            return;
        }

        self::assertTrue($this->getResourceTransformer()->supportsPage($this->getResourceClass(), [
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
            'hydra:totalItems' => 2,
            'hydra:view' => [
                '@type' => 'hydra:PartialCollectionView',
                'next' => 'https://foo.com/dms?page=2&itemsPerPage=1',
            ],
        ]));
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testSupportsCorrectPageStructuresWithNextAndPrevious(array $structure): void
    {
        if (!$this->supportsPage()) {
            self::expectNotToPerformAssertions();

            return;
        }

        self::assertTrue($this->getResourceTransformer()->supportsPage($this->getResourceClass(), [
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
            'hydra:totalItems' => 3,
            'hydra:view' => [
                '@type' => 'hydra:PartialCollectionView',
                'next' => 'https://foo.com/dms?page=3&itemsPerPage=1',
                'previous' => 'https://foo.com/dms?page=1&itemsPerPage=1',
            ],
        ]));
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testSupportsCorrectPageStructuresWithPrevious(array $structure): void
    {
        if (!$this->supportsPage()) {
            self::expectNotToPerformAssertions();

            return;
        }

        self::assertTrue($this->getResourceTransformer()->supportsPage($this->getResourceClass(), [
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
            'hydra:totalItems' => 2,
            'hydra:view' => [
                '@type' => 'hydra:PartialCollectionView',
                'previous' => 'https://foo.com/dms?page=1&itemsPerPage=1',
            ],
        ]));
    }

    /**
     * @dataProvider getIncorrectStructures
     */
    public function testDoesNotSupportIncorrectPageStructures(array $structure): void
    {
        if (!$this->supportsPage()) {
            self::expectNotToPerformAssertions();

            return;
        }

        self::assertFalse($this->getResourceTransformer()->supportsPage($this->getResourceClass(), [
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
            'hydra:totalItems' => 2,
            'hydra:view' => [
                '@type' => 'hydra:PartialCollectionView',
                'previous' => 'https://foo.com/dms?page=1&itemsPerPage=1',
            ],
        ]));
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testDoesNotSupportIncorrectPageStructuresWithNonSupportedResource(array $structure): void
    {
        self::assertFalse($this->getResourceTransformer()->supportsPage(stdClass::class, [
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
            'hydra:totalItems' => 2,
            'hydra:view' => [
                '@type' => 'hydra:PartialCollectionView',
                'previous' => 'https://foo.com/dms?page=1&itemsPerPage=1',
            ],
        ]));
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testTransformPage(array $structure): void
    {
        if (!$this->supportsPage()) {
            self::expectNotToPerformAssertions();

            return;
        }

        $page = $this->getResourceTransformer()->transformPage([
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
            'hydra:totalItems' => 1,
            'hydra:view' => [
                '@type' => 'hydra:PartialCollectionView',
            ],
        ], 1, 1);

        self::assertFalse($page->hasNext);
        self::assertFalse($page->hasPrevious);
        self::assertSame(1, $page->totalItems);
        self::assertSame(1, $page->itemsPerPage);
        self::assertNull($page->getNextPage());
        self::assertNull($page->getPreviousPage());
        self::assertSame(1, $page->getLastPage());

        $collection = $page->items;

        self::assertCount(1, $collection);

        $document = $collection->at(0);

        $this->assertResourceMatchesStructure($document, $structure, true);
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testTransformPageWithPrevious(array $structure): void
    {
        if (!$this->supportsPage()) {
            self::expectNotToPerformAssertions();

            return;
        }

        $page = $this->getResourceTransformer()->transformPage([
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
            'hydra:totalItems' => 2,
            'hydra:view' => [
                '@type' => 'hydra:PartialCollectionView',
                'hydra:previous' => 'https://uri',
            ],
        ], 2, 1);

        self::assertFalse($page->hasNext);
        self::assertTrue($page->hasPrevious);
        self::assertSame(2, $page->totalItems);
        self::assertSame(1, $page->itemsPerPage);
        self::assertNull($page->getNextPage());
        self::assertSame(1, $page->getPreviousPage());
        self::assertSame(2, $page->getLastPage());

        $collection = $page->items;

        self::assertCount(1, $collection);

        $document = $collection->at(0);

        $this->assertResourceMatchesStructure($document, $structure, true);
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testTransformPageWithNext(array $structure): void
    {
        if (!$this->supportsPage()) {
            self::expectNotToPerformAssertions();

            return;
        }

        $page = $this->getResourceTransformer()->transformPage([
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
            'hydra:totalItems' => 2,
            'hydra:view' => [
                '@type' => 'hydra:PartialCollectionView',
                'hydra:next' => 'https://uri',
            ],
        ], 1, 1);

        self::assertTrue($page->hasNext);
        self::assertFalse($page->hasPrevious);
        self::assertSame(2, $page->totalItems);
        self::assertSame(1, $page->itemsPerPage);
        self::assertSame(2, $page->getNextPage());
        self::assertNull($page->getPreviousPage());
        self::assertSame(2, $page->getLastPage());

        $collection = $page->items;

        self::assertCount(1, $collection);

        $document = $collection->at(0);
        $this->assertResourceMatchesStructure($document, $structure, true);
    }

    /**
     * @dataProvider getCorrectStructures
     */
    public function testTransformPageWithNextAndPrevious(array $structure): void
    {
        if (!$this->supportsPage()) {
            self::expectNotToPerformAssertions();

            return;
        }

        $page = $this->getResourceTransformer()->transformPage([
            '@type' => 'hydra:Collection',
            'hydra:member' => [$structure],
            'hydra:totalItems' => 4,
            'hydra:view' => [
                '@type' => 'hydra:PartialCollectionView',
                'hydra:next' => 'https://uri',
                'hydra:previous' => 'https://uri',
            ],
        ], 2, 1);

        self::assertTrue($page->hasNext);
        self::assertTrue($page->hasPrevious);
        self::assertSame(4, $page->totalItems);
        self::assertSame(1, $page->itemsPerPage);
        self::assertSame(3, $page->getNextPage());
        self::assertSame(1, $page->getPreviousPage());
        self::assertSame(4, $page->getLastPage());

        $collection = $page->items;

        self::assertCount(1, $collection);

        $document = $collection->at(0);
        $this->assertResourceMatchesStructure($document, $structure, true);
    }

    /**
     * @return class-string<T>
     */
    abstract protected function getResourceClass(): string;

    /**
     * @return ResourceTransformerInterface<T>
     */
    abstract protected function getResourceTransformer(): ResourceTransformerInterface;

    /**
     * @param T $resource
     */
    abstract protected function assertResourceMatchesStructure(ResourceInterface $resource, array $structure, bool $fromCollectionOrPage): void;

    abstract protected function supportsCollections(): bool;

    abstract protected function supportsPage(): bool;
}
