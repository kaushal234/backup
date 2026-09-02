<?php

declare(strict_types=1);

namespace App\Tests\ExternalERP\Resolver;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ExternalERP\Resolver\OperationResolver;
use App\ION\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\ION\SourceProvider\SourceProvider;
use PHPUnit\Framework\TestCase;

final class OperationResolverTest extends TestCase
{
    public function testResolveReturnsItemOperationForGet(): void
    {
        $operation = new Get(class: \stdClass::class);
        $uriVariables = ['id' => 42];

        $resourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);
        $resourceSourceProvider
            ->expects(self::once())
            ->method('getRestItemReadOperation')
            ->with($uriVariables)
            ->willReturn('/items/42');

        $resourceSourceProvider
            ->expects(self::never())
            ->method('getRestCollectionReadOperation');

        $sourceProvider = $this->createMock(SourceProvider::class);
        $sourceProvider
            ->method('getResourceSourceProvider')
            ->with(\stdClass::class)
            ->willReturn($resourceSourceProvider);

        $resolver = new OperationResolver($sourceProvider);

        $result = $resolver->resolve($operation, $uriVariables);

        self::assertSame('/items/42', $result);
    }

    public function testResolveReturnsCollectionOperationForGetCollection(): void
    {
        $operation = new GetCollection(class: \stdClass::class);

        $resourceSourceProvider = $this->createMock(ResourceSourceProviderInterface::class);
        $resourceSourceProvider
            ->expects(self::once())
            ->method('getRestCollectionReadOperation')
            ->willReturn('/items');

        $resourceSourceProvider
            ->expects(self::never())
            ->method('getRestItemReadOperation');

        $sourceProvider = $this->createMock(SourceProvider::class);
        $sourceProvider
            ->method('getResourceSourceProvider')
            ->with(\stdClass::class)
            ->willReturn($resourceSourceProvider);

        $resolver = new OperationResolver($sourceProvider);

        $result = $resolver->resolve($operation);

        self::assertSame('/items', $result);
    }
}
