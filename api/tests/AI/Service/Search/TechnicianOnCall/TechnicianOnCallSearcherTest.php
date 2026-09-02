<?php

declare(strict_types=1);

namespace App\Tests\AI\Service\Search\TechnicianOnCall;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Dto\Result;
use App\AI\Dto\TechnicianOnCallSearch;
use App\AI\Exception\NoResultException;
use App\AI\Factory\AILogFactory;
use App\AI\Handler\SearchSourceHandlerInterface;
use App\AI\Service\Search\TechnicianOnCall\TechnicianOnCallSearcher;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Vector\VectorInterface;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\AI\Store\RetrieverInterface;

final class TechnicianOnCallSearcherTest extends TestCase
{
    public function testSupportsTrueForTechnicianOnCallSearch(): void
    {
        self::assertTrue($this->makeSearcher($this->createMock(RetrieverInterface::class))->supports(TechnicianOnCallSearch::class));
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

    public function testSearchReturnsSearchResultWhenHandlerReturnsItem(): void
    {
        $searchResult = new Result(id: 55, module: 'toc', description: 'Technician on call for engine repair', link: '/toc/view/55');

        $handler = $this->createMock(SearchSourceHandlerInterface::class);
        $handler->method('handle')
            ->with(['id' => 55, 'src' => 'toc'])
            ->willReturn($searchResult);

        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([$this->makeDoc(55, 0.9)]);

        $output = $this->makeSearcher($retriever, $handler)->search('query');

        self::assertCount(1, $output->results);
        self::assertSame(55, $output->results[0]->id);
        self::assertSame('toc', $output->results[0]->module);
        self::assertSame('Technician on call for engine repair', $output->results[0]->description);
        self::assertSame('/toc/view/55', $output->results[0]->link);
    }

    public function testSearchThrowsNoResultExceptionWhenHandlerReturnsNull(): void
    {
        $handler = $this->createMock(SearchSourceHandlerInterface::class);
        $handler->method('handle')->willReturn(null);

        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([$this->makeDoc(99, 0.8)]);

        $this->expectException(NoResultException::class);
        $this->makeSearcher($retriever, $handler)->search('query');
    }

    public function testSearchDeduplicatesByTocIdKeepingHighestScore(): void
    {
        $callCount = 0;
        $handler = $this->createMock(SearchSourceHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(static function (array $data) use (&$callCount): Result {
            ++$callCount;

            return new Result(id: $data['id'], module: $data['src'], description: 'desc', link: '/toc');
        });

        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc(1, 0.5),
            $this->makeDoc(1, 0.9),
            $this->makeDoc(2, 0.8),
        ]);

        $output = $this->makeSearcher($retriever, $handler)->search('query');

        self::assertCount(2, $output->results);
        self::assertSame(2, $callCount);
    }

    public function testSearchRespectsLimit(): void
    {
        $handler = $this->createMock(SearchSourceHandlerInterface::class);
        $handler->method('handle')->willReturnCallback(
            static fn (array $data): Result => new Result(id: $data['id'], module: $data['src'], description: 'desc', link: '/toc'),
        );

        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc(1, 0.9),
            $this->makeDoc(2, 0.8),
            $this->makeDoc(3, 0.7),
            $this->makeDoc(4, 0.6),
        ]);

        $output = $this->makeSearcher($retriever, $handler)->search('query', limit: 2);

        self::assertCount(2, $output->results);
    }

    private function makeSearcher(
        RetrieverInterface $retriever,
        ?SearchSourceHandlerInterface $handler = null,
    ): TechnicianOnCallSearcher {
        return new TechnicianOnCallSearcher(
            $retriever,
            $this->createMock(AILogFactory::class),
            $this->createMock(IriConverterInterface::class),
            $handler ?? $this->createMock(SearchSourceHandlerInterface::class),
        );
    }

    private function makeDoc(int $tocId, float $score): VectorDocument
    {
        return (new VectorDocument(
            id: (string) $tocId,
            vector: $this->createMock(VectorInterface::class),
            metadata: new Metadata(['meta' => ['tocid' => $tocId]]),
        ))->withScore($score);
    }
}
