<?php

declare(strict_types=1);

namespace App\Tests\AI\Service\Search\Intranet;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Dto\IntranetSearch;
use App\AI\Dto\Result;
use App\AI\Exception\NoResultException;
use App\AI\Factory\AILogFactory;
use App\AI\Handler\SearchSourceHandlerInterface;
use App\AI\Service\Search\Intranet\IntranetSearcher;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Vector\VectorInterface;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\AI\Store\RetrieverInterface;

final class IntranetSearcherTest extends TestCase
{
    public function testSupportsTrueForIntranetSearch(): void
    {
        self::assertTrue($this->makeSearcher($this->createMock(RetrieverInterface::class))->supports(IntranetSearch::class));
    }

    public function testSupportsFalseForOtherClass(): void
    {
        self::assertFalse($this->makeSearcher($this->createMock(RetrieverInterface::class))->supports(\stdClass::class));
    }

    public function testSearchThrowsNoResultExceptionWhenRetrieverReturnsNothing(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([]);

        $this->expectException(NoResultException::class);
        $this->makeSearcher($retriever)->search('query');
    }

    public function testSearchReturnsResultFromMatchingHandler(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc(['src' => 'WIKI', 'id' => 1], 0.9),
        ]);

        $searchResult = new Result(id: 1, module: 'WIKI', description: 'A wiki page', link: '/wiki/1');

        $handler = $this->createMock(SearchSourceHandlerInterface::class);
        $handler->method('supports')->with('wiki')->willReturn(true);
        $handler->method('handle')->with(['src' => 'WIKI', 'id' => 1])->willReturn($searchResult);

        $output = $this->makeSearcher($retriever, [$handler])->search('query');

        self::assertCount(1, $output->results);
        self::assertSame('WIKI', $output->results[0]->module);
        self::assertSame('A wiki page', $output->results[0]->description);
        self::assertSame('/wiki/1', $output->results[0]->link);
    }

    public function testSearchThrowsNoResultExceptionWhenHandlerReturnsNull(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc(['src' => 'FAQ', 'id' => 1], 0.8),
        ]);

        $handler = $this->createMock(SearchSourceHandlerInterface::class);
        $handler->method('supports')->willReturn(true);
        $handler->method('handle')->willReturn(null);

        $this->expectException(NoResultException::class);
        $this->makeSearcher($retriever, [$handler])->search('query');
    }

    public function testSearchThrowsNoResultExceptionWhenNoHandlerSupports(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc(['src' => 'UNKNOWN', 'id' => 1], 0.8),
        ]);

        $handler = $this->createMock(SearchSourceHandlerInterface::class);
        $handler->method('supports')->willReturn(false);
        $handler->expects(self::never())->method('handle');

        $this->expectException(NoResultException::class);
        $this->makeSearcher($retriever, [$handler])->search('query');
    }

    public function testSearchDeduplicatesBySrcAndIdKeepingHighestScore(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc(['src' => 'WIKI', 'id' => 1], 0.5),
            $this->makeDoc(['src' => 'WIKI', 'id' => 1], 0.9),
            $this->makeDoc(['src' => 'FAQ', 'id' => 2], 0.8),
        ]);

        $callCount = 0;
        $handler = $this->createMock(SearchSourceHandlerInterface::class);
        $handler->method('supports')->willReturn(true);
        $handler->method('handle')->willReturnCallback(static function (array $data) use (&$callCount): Result {
            ++$callCount;

            return new Result(id: $data['id'], module: $data['src'], description: 'desc', link: '/link');
        });

        $output = $this->makeSearcher($retriever, [$handler])->search('query');

        self::assertCount(2, $output->results);
        self::assertSame(2, $callCount);
    }

    public function testSearchRespectsLimit(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc(['src' => 'WIKI', 'id' => 1], 0.9),
            $this->makeDoc(['src' => 'WIKI', 'id' => 2], 0.8),
            $this->makeDoc(['src' => 'WIKI', 'id' => 3], 0.7),
            $this->makeDoc(['src' => 'WIKI', 'id' => 4], 0.6),
        ]);

        $handler = $this->createMock(SearchSourceHandlerInterface::class);
        $handler->method('supports')->willReturn(true);
        $handler->method('handle')->willReturnCallback(
            static fn (array $data): Result => new Result(id: $data['id'], module: $data['src'], description: 'desc', link: '/link'),
        );

        $output = $this->makeSearcher($retriever, [$handler])->search('query', limit: 2);

        self::assertCount(2, $output->results);
    }

    /**
     * @param list<SearchSourceHandlerInterface> $handlers
     */
    private function makeSearcher(
        RetrieverInterface $retriever,
        array $handlers = [],
    ): IntranetSearcher {
        return new IntranetSearcher(
            $retriever,
            $this->createMock(AILogFactory::class),
            $this->createMock(IriConverterInterface::class),
            $handlers,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function makeDoc(array $metadata, float $score): VectorDocument
    {
        return (new VectorDocument(
            id: \sprintf('%s_%s', $metadata['src'] ?? '', $metadata['id'] ?? ''),
            vector: $this->createMock(VectorInterface::class),
            metadata: new Metadata(['meta' => $metadata]),
        ))->withScore($score);
    }
}
