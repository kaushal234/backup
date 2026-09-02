<?php

declare(strict_types=1);

namespace App\Tests\AI\DataProvider\Search;

use ApiPlatform\Metadata\Operation;
use App\AI\DataProvider\Search\SearchDataProvider;
use App\AI\Dto\SearchOutput;
use App\AI\Exception\NoResultException;
use App\AI\Service\Search\SearcherInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class SearchDataProviderTest extends TestCase
{
    public function testProvideThrowsBadRequestWhenQueryParamIsMissing(): void
    {
        $requestStack = $this->makeRequestStack(Request::create('/ai/search/dms'));

        $this->expectException(BadRequestHttpException::class);

        $this->makeProvider($requestStack)->provide($this->makeOperation());
    }

    public function testProvideReturnsNullWhenNoSearcherSupportsTheClass(): void
    {
        $operation = $this->makeOperation(\stdClass::class);

        $searcher = $this->createMock(SearcherInterface::class);
        $searcher->method('supports')->with(\stdClass::class)->willReturn(false);
        $searcher->expects(self::never())->method('search');

        $result = $this->makeProvider(
            searchQuery: 'turbine',
            searchers: [$searcher],
        )->provide($operation);

        self::assertNull($result);
    }

    public function testProvideReturnsNullWhenSearcherThrowsNoResultException(): void
    {
        $operation = $this->makeOperation(\stdClass::class);

        $searcher = $this->createMock(SearcherInterface::class);
        $searcher->method('supports')->willReturn(true);
        $searcher->method('search')->willThrowException(new NoResultException());

        $result = $this->makeProvider(
            searchQuery: 'turbine',
            searchers: [$searcher],
        )->provide($operation);

        self::assertNull($result);
    }

    public function testProvideReturnsSearchOutputFromMatchingSearcher(): void
    {
        $operation = $this->makeOperation(\stdClass::class);
        $expected = new SearchOutput();

        $searcher = $this->createMock(SearcherInterface::class);
        $searcher->method('supports')->willReturn(true);
        $searcher->method('search')
            ->with('turbine', 10, true)
            ->willReturn($expected);

        $result = $this->makeProvider(
            searchQuery: 'turbine',
            searchers: [$searcher],
        )->provide($operation);

        self::assertSame($expected, $result);
    }

    public function testProvideSkipsNonMatchingSearchersAndUsesFirstMatch(): void
    {
        $operation = $this->makeOperation(\stdClass::class);
        $expected = new SearchOutput();

        $nonMatching = $this->createMock(SearcherInterface::class);
        $nonMatching->method('supports')->willReturn(false);
        $nonMatching->expects(self::never())->method('search');

        $matching = $this->createMock(SearcherInterface::class);
        $matching->method('supports')->willReturn(true);
        $matching->method('search')->willReturn($expected);

        $result = $this->makeProvider(
            searchQuery: 'turbine',
            searchers: [$nonMatching, $matching],
        )->provide($operation);

        self::assertSame($expected, $result);
    }

    private function makeOperation(string $class = \stdClass::class): Operation
    {
        $operation = $this->createMock(Operation::class);
        $operation->method('getClass')->willReturn($class);

        return $operation;
    }

    private function makeRequestStack(Request $request): RequestStack
    {
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        return $requestStack;
    }

    private function makeProvider(
        ?RequestStack $requestStack = null,
        string $searchQuery = 'query',
        array $searchers = [],
    ): SearchDataProvider {
        if (null === $requestStack) {
            $requestStack = $this->makeRequestStack(
                Request::create('/ai/search', 'GET', ['query' => $searchQuery]),
            );
        }

        return new SearchDataProvider($requestStack, $searchers);
    }
}
