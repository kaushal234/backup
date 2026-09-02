<?php

declare(strict_types=1);

namespace App\Tests\ION\DataProvider;

use ApiPlatform\Metadata\GetCollection;
use App\ExternalERP\Filter\ContainsFilter;
use App\ExternalERP\Filter\EqualsFilter;
use App\ExternalERP\Mapping\Mapper\FieldMapper;
use App\ExternalERP\Resolver\OperationResolverInterface;
use App\Http\LnClient;
use App\ION\DataProvider\CollectionDataProvider;
use App\ION\Event\IONPreRequestEvent;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class CollectionDataProviderTest extends TestCase
{
    public function testBuildFiltersWithContainsFilterAndMapping(): void
    {
        $request = new Request([ContainsFilter::FILTER_PROPERTY => ['name' => 'john']]);

        $provider = $this->createProvider();
        $filters = $provider->buildFilters($request, ['name' => 'full_name']);

        self::assertSame(
            ["contains(full_name,'john')"],
            $filters
        );
    }

    public function testBuildFiltersWithEqualsFilterWithoutMapping(): void
    {
        $request = new Request([EqualsFilter::FILTER_PROPERTY => ['status' => 'active']]);
        $provider = $this->createProvider();
        $filters = $provider->buildFilters($request, []);

        self::assertSame(
            ['status eq active'],
            $filters
        );
    }

    public function testBuildFiltersWithMixedFilters(): void
    {
        $request = new Request([
            ContainsFilter::FILTER_PROPERTY => ['name' => 'john'],
            EqualsFilter::FILTER_PROPERTY => ['status' => 'active'],
        ]);

        $provider = $this->createProvider();
        $filters = $provider->buildFilters($request, ['name' => 'full_name']);

        self::assertSame(
            [
                "contains(full_name,'john')",
                'status eq active',
            ],
            $filters
        );
    }

    public function testProvideCallsClientWithBuiltFiltersAndDenormalizes(): void
    {
        $request = new Request([
            ContainsFilter::FILTER_PROPERTY => ['name' => 'john'],
        ]);

        $fieldMapper = $this->createMock(FieldMapper::class);
        $fieldMapper
            ->method('getMapping')
            ->willReturn(['name' => 'full_name']);

        $operationResolver = $this->createMock(OperationResolverInterface::class);
        $operationResolver
            ->method('resolve')
            ->willReturn('/dummy-endpoint');

        $response = new Response(json_encode([
            'value' => [
                ['id' => 1],
            ],
        ]));

        $client = $this->createMock(LnClient::class);
        $client
            ->expects(self::once())
            ->method('doRequest')
            ->with(
                '/dummy-endpoint',
                ["contains(full_name,'john')"]
            )
            ->willReturn($response);

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer
            ->expects(self::once())
            ->method('denormalize')
            ->with(
                [['id' => 1]],
                \stdClass::class.'[]',
                'jsonld',
                self::arrayHasKey('request')
            )
            ->willReturn([]);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(IONPreRequestEvent::class))
            ->willReturnCallback(static fn ($event) => $event);

        $provider = new CollectionDataProvider(
            $client,
            $operationResolver,
            $denormalizer,
            $fieldMapper,
            $eventDispatcher
        );

        $operation = new GetCollection(class: \stdClass::class);

        $result = $provider->provide($operation, [], [
            'request' => $request,
        ]);

        self::assertSame([], $result);
    }

    public function testMappedFieldsAreUsedInClientRequest(): void
    {
        $request = new Request([
            EqualsFilter::FILTER_PROPERTY => ['email' => 'test@example.com'],
        ]);

        $fieldMapper = $this->createMock(FieldMapper::class);
        $fieldMapper
            ->method('getMapping')
            ->willReturn(['email' => 'contact_email']);

        $operationResolver = $this->createMock(OperationResolverInterface::class);
        $operationResolver
            ->expects(self::once())
            ->method('resolve')
            ->willReturn('/dummy-endpoint');

        $response = new Response(json_encode([
            'value' => [],
        ]), Response::HTTP_OK);

        $client = $this->createMock(LnClient::class);
        $client
            ->expects(self::once())
            ->method('doRequest')
            ->with(
                '/dummy-endpoint',
                [
                    'contact_email eq test@example.com',
                ]
            )
            ->willReturn($response);

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer
            ->expects(self::once())
            ->method('denormalize')
            ->with(
                [],
                \stdClass::class.'[]',
                'jsonld',
                self::arrayHasKey('request')
            )
            ->willReturn([]);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(IONPreRequestEvent::class))
            ->willReturnCallback(static fn ($event) => $event);

        $provider = new CollectionDataProvider(
            $client,
            $operationResolver,
            $denormalizer,
            $fieldMapper,
            $eventDispatcher
        );

        $operation = new GetCollection(class: \stdClass::class);

        $result = $provider->provide($operation, [], [
            'request' => $request,
        ]);

        self::assertSame([], $result);
    }

    private function createProvider(): CollectionDataProvider
    {
        return new CollectionDataProvider(
            $this->createMock(LnClient::class),
            $this->createMock(OperationResolverInterface::class),
            $this->createMock(DenormalizerInterface::class),
            $this->createMock(FieldMapper::class),
            $this->createMock(EventDispatcherInterface::class)
        );
    }
}
