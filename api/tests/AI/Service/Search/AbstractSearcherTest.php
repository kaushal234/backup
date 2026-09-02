<?php

declare(strict_types=1);

namespace App\Tests\AI\Service\Search;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Dto\Result;
use App\AI\Dto\SearchOutput;
use App\AI\Exception\NoResultException;
use App\AI\Factory\AILogFactory;
use App\AI\Service\Search\AbstractSearcher;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Vector\VectorInterface;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\AI\Store\RetrieverInterface;

final class AbstractSearcherTest extends TestCase
{
    public function testSearchCallsRetrieverWithLimitMultipliedByTwenty(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever
            ->expects(self::once())
            ->method('retrieve')
            ->with('hello', ['limit' => 100])
            ->willReturn([]);

        $this->expectException(NoResultException::class);
        $this->makeSearcher($retriever)->search('hello', limit: 5);
    }

    public function testSearchThrowsNoResultExceptionWhenRetrieverReturnsNothing(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([]);

        $this->expectException(NoResultException::class);
        $this->makeSearcher($retriever)->search('query');
    }

    public function testSearchThrowsNoResultExceptionWhenAllDocumentsLackUniqueId(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc([], 0.9),
            $this->makeDoc([], 0.8),
        ]);

        $this->expectException(NoResultException::class);
        $this->makeSearcher($retriever)->search('query');
    }

    public function testSearchSkipsDocumentsWithoutUniqueId(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc([], 0.9),           // no 'id' key → skipped
            $this->makeDoc(['id' => 1], 0.8),
        ]);

        $output = $this->makeSearcher($retriever)->search('query');

        self::assertCount(1, $output->results);
    }

    public function testSearchDeduplicatesByUniqueIdKeepingHighestScore(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc(['id' => 42], 0.5),
            $this->makeDoc(['id' => 42], 0.9), // higher score — should win
            $this->makeDoc(['id' => 99], 0.7),
        ]);

        $output = $this->makeSearcher($retriever)->search('query');

        // Only 2 unique ids
        self::assertCount(2, $output->results);
    }

    public function testSearchRespectsLimit(): void
    {
        $docs = [];
        for ($i = 1; $i <= 5; ++$i) {
            $docs[] = $this->makeDoc(['id' => $i], (float) $i / 10);
        }

        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn($docs);

        $output = $this->makeSearcher($retriever)->search('query', limit: 3);

        self::assertCount(3, $output->results);
    }

    public function testSearchReturnsSearchOutputWithNullLogIriByDefault(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc(['id' => 1], 0.9),
        ]);

        $output = $this->makeSearcher($retriever)->search('query');

        self::assertInstanceOf(SearchOutput::class, $output);
        self::assertNull($output->logIri);
    }

    public function testSupportsReturnsTrueForMatchingClass(): void
    {
        self::assertTrue($this->makeSearcher($this->createMock(RetrieverInterface::class))->supports(\stdClass::class));
    }

    public function testSupportsReturnsFalseForOtherClass(): void
    {
        self::assertFalse($this->makeSearcher($this->createMock(RetrieverInterface::class))->supports(\ArrayObject::class));
    }

    private function makeSearcher(RetrieverInterface $retriever): ConcreteSearcher
    {
        return new ConcreteSearcher(
            $retriever,
            $this->createMock(AILogFactory::class),
            $this->createMock(IriConverterInterface::class),
        );
    }

    private function makeDoc(array $metadata, float $score): VectorDocument
    {
        return (new VectorDocument(
            id: isset($metadata['id']) ? (string) $metadata['id'] : 'no-id',
            vector: $this->createMock(VectorInterface::class),
            metadata: new Metadata(['meta' => $metadata]),
        ))->withScore($score);
    }
}

final class ConcreteSearcher extends AbstractSearcher
{
    protected function getDtoClass(): string
    {
        return \stdClass::class;
    }

    protected function getUniqueId(array $metadata): ?string
    {
        return isset($metadata['id']) ? (string) $metadata['id'] : null;
    }

    protected function processResults(array $results): Collection
    {
        return new ArrayCollection(array_map(
            static fn (array $metadata): Result => new Result(
                id: $metadata['id'],
                module: 'test',
                description: 'desc',
                link: '/link',
            ),
            $results,
        ));
    }

    protected function getOperation(): string
    {
        return '/test';
    }
}
