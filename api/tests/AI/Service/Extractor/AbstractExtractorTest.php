<?php

declare(strict_types=1);

namespace App\Tests\AI\Service\Extractor;

use App\AI\Exception\AccessDeniedException;
use App\AI\Exception\EntityNotFoundException;
use App\AI\Factory\ModelFactoryInterface;
use App\AI\Security\EntityAccessCheckerInterface;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\AI\Service\Extractor\AbstractExtractor;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

final class AbstractExtractorTest extends TestCase
{
    public function testExtractReturnsSerializedJson(): void
    {
        $object = new \stdClass();
        $model = new \stdClass();
        $model->foo = 'bar';

        $registry = $this->makeRegistry(\stdClass::class, ['id' => 7], $object);

        $factory = $this->createMock(ModelFactoryInterface::class);
        $factory->method('supports')->with(\stdClass::class)->willReturn(true);
        $factory->expects(self::once())->method('create')->with($object)->willReturn($model);

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->expects(self::once())
            ->method('serialize')
            ->with($model, 'json')
            ->willReturn('{"foo":"bar"}');

        $extractor = new TestExtractor($registry, [$factory], $serializer, $this->makeAccessCheckers(true));

        self::assertSame('{"foo":"bar"}', $extractor->extract(\stdClass::class, ['id' => 7]));
    }

    public function testExtractThrowsEntityNotFoundExceptionWhenNoObject(): void
    {
        $registry = $this->makeRegistry(\stdClass::class, ['id' => 99], null);
        $factory = $this->createMock(ModelFactoryInterface::class);
        $factory->expects(self::never())->method('create');

        $extractor = new TestExtractor(
            $registry,
            [$factory],
            $this->createMock(SerializerInterface::class),
            $this->makeAccessCheckers(true),
        );

        $this->expectException(EntityNotFoundException::class);
        $extractor->extract(\stdClass::class, ['id' => 99]);
    }

    public function testExtractThrowsAccessDeniedExceptionWhenNotGranted(): void
    {
        $registry = $this->makeRegistry(\stdClass::class, ['id' => 42], new \stdClass());
        $factory = $this->createMock(ModelFactoryInterface::class);
        $factory->expects(self::never())->method('create');

        $extractor = new TestExtractor(
            $registry,
            [$factory],
            $this->createMock(SerializerInterface::class),
            $this->makeAccessCheckers(false),
        );

        $this->expectException(AccessDeniedException::class);
        $extractor->extract(\stdClass::class, ['id' => 42]);
    }

    public function testExtractThrowsLogicExceptionWhenNoSupportingFactory(): void
    {
        $registry = $this->makeRegistry(\stdClass::class, ['id' => 1], new \stdClass());

        $unrelated = $this->createMock(ModelFactoryInterface::class);
        $unrelated->method('supports')->willReturn(false);

        $extractor = new TestExtractor(
            $registry,
            [$unrelated],
            $this->createMock(SerializerInterface::class),
            $this->makeAccessCheckers(true),
        );

        $this->expectException(\LogicException::class);
        $extractor->extract(\stdClass::class, ['id' => 1]);
    }

    public function testExtractPicksFirstFactorySupportingClass(): void
    {
        $object = new \stdClass();
        $model = new \stdClass();

        $registry = $this->makeRegistry(\stdClass::class, ['id' => 5], $object);

        $unsupported = $this->createMock(ModelFactoryInterface::class);
        $unsupported->method('supports')->willReturn(false);
        $unsupported->expects(self::never())->method('create');

        $supported = $this->createMock(ModelFactoryInterface::class);
        $supported->method('supports')->with(\stdClass::class)->willReturn(true);
        $supported->expects(self::once())->method('create')->with($object)->willReturn($model);

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('serialize')->willReturn('{}');

        $extractor = new TestExtractor(
            $registry,
            [$unsupported, $supported],
            $serializer,
            $this->makeAccessCheckers(true),
        );

        self::assertSame('{}', $extractor->extract(\stdClass::class, ['id' => 5]));
    }

    /**
     * @param class-string        $class
     * @param array<string,mixed> $criteria
     */
    private function makeRegistry(string $class, array $criteria, ?object $found): ManagerRegistry
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository->expects(self::once())->method('findOneBy')->with($criteria)->willReturn($found);

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects(self::once())->method('getRepository')->with($class)->willReturn($repository);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects(self::once())->method('getManagerForClass')->with($class)->willReturn($manager);

        return $registry;
    }

    private function makeAccessCheckers(bool $granted): EntityAccessCheckerRegistry
    {
        $checker = $this->createMock(EntityAccessCheckerInterface::class);
        $checker->method('supports')->willReturn(true);
        $checker->method('isGranted')->willReturn($granted);

        return new EntityAccessCheckerRegistry([$checker]);
    }
}

final class TestExtractor extends AbstractExtractor
{
}
