<?php

declare(strict_types=1);

namespace App\Tests\ION\DataProvider;

use ApiPlatform\Metadata\Get;
use App\ExternalERP\Resolver\OperationResolverInterface;
use App\Http\LnClient;
use App\ION\DataProvider\ItemDataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

final class ItemDataProviderTest extends TestCase
{
    public function testReturnsNullWhenClientThrowsException(): void
    {
        $operation = new Get(class: \stdClass::class);

        $operationResolver = $this->createMock(OperationResolverInterface::class);
        $operationResolver
            ->method('resolve')
            ->willReturn('/fake-operation');

        $client = $this->createMock(LnClient::class);
        $client
            ->expects(self::once())
            ->method('doRequest')
            ->with('/fake-operation')
            ->willThrowException(new \RuntimeException());

        $serializer = $this->createMock(SerializerInterface::class);

        $provider = new ItemDataProvider(
            $client,
            $operationResolver,
            $serializer
        );

        self::assertNull($provider->provide($operation));
    }

    public function testReturnsNullWhenStatusIsGreaterThan400(): void
    {
        $operation = new Get(class: \stdClass::class);

        $operationResolver = $this->createMock(OperationResolverInterface::class);
        $operationResolver
            ->method('resolve')
            ->willReturn('/fake-operation');

        $response = new Response('', Response::HTTP_NOT_FOUND);

        $client = $this->createMock(LnClient::class);
        $client
            ->expects(self::once())
            ->method('doRequest')
            ->with('/fake-operation')
            ->willReturn($response);

        $serializer = $this->createMock(SerializerInterface::class);

        $provider = new ItemDataProvider(
            $client,
            $operationResolver,
            $serializer
        );

        self::assertNull($provider->provide($operation));
    }

    public function testDeserializeIsCalledAndObjectReturned(): void
    {
        $operation = new Get(
            class: \stdClass::class,
            normalizationContext: [
                AbstractNormalizer::GROUPS => ['read'],
            ]
        );

        $operationResolver = $this->createMock(OperationResolverInterface::class);
        $operationResolver
            ->method('resolve')
            ->willReturn('/fake-operation');

        $response = new Response('{"id":1}', Response::HTTP_OK);

        $client = $this->createMock(LnClient::class);
        $client
            ->expects(self::once())
            ->method('doRequest')
            ->with('/fake-operation')
            ->willReturn($response);

        $expected = new \stdClass();

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with(
                '{"id":1}',
                \stdClass::class,
                'jsonld',
                self::arrayHasKey('operation')
            )
            ->willReturn($expected);

        $provider = new ItemDataProvider(
            $client,
            $operationResolver,
            $serializer
        );

        self::assertSame($expected, $provider->provide($operation));
    }

    public function testNormalizationContextIsMergedWhenGroupsAreMissing(): void
    {
        $operation = new Get(
            class: \stdClass::class,
            normalizationContext: [
                AbstractNormalizer::GROUPS => ['read'],
            ]
        );

        $operationResolver = $this->createMock(OperationResolverInterface::class);
        $operationResolver
            ->method('resolve')
            ->willReturn('/fake-operation');

        $response = new Response('{}', Response::HTTP_OK);

        $client = $this->createMock(LnClient::class);
        $client
            ->expects(self::once())
            ->method('doRequest')
            ->with('/fake-operation')
            ->willReturn($response);

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->expects(self::once())
            ->method('deserialize')
            ->with(
                '{}',
                \stdClass::class,
                'jsonld',
                self::callback(
                    static fn (array $context): bool => isset($context[AbstractNormalizer::GROUPS])
                        && $context[AbstractNormalizer::GROUPS] === ['read']
                )
            )
            ->willReturn(new \stdClass());

        $provider = new ItemDataProvider(
            $client,
            $operationResolver,
            $serializer
        );

        $provider->provide($operation);
    }
}
