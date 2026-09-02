<?php

declare(strict_types=1);

namespace App\Tests\AI\Service\Extractor;

use App\AI\Factory\ModelFactoryInterface;
use App\AI\Security\EntityAccessCheckerInterface;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\AI\Service\Extractor\CustomExtractorInterface;
use App\AI\Service\Extractor\GenericExtractor;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

final class GenericExtractorTest extends TestCase
{
    public function testDelegatesToCustomExtractorWhenSupported(): void
    {
        $custom = $this->createMock(CustomExtractorInterface::class);
        $custom->expects(self::once())->method('supports')->with(\stdClass::class)->willReturn(true);
        $custom->expects(self::once())
            ->method('extract')
            ->with(\stdClass::class, ['id' => 42])
            ->willReturn('custom result');

        $extractor = new GenericExtractor(
            $this->createMock(ManagerRegistry::class),
            [],
            $this->createMock(SerializerInterface::class),
            $this->makeAccessCheckers(true),
            [$custom],
        );

        self::assertSame('custom result', $extractor->extract(\stdClass::class, ['id' => 42]));
    }

    public function testFallsBackToParentWhenNoCustomSupports(): void
    {
        $object = new \stdClass();
        $model = new \stdClass();

        $custom = $this->createMock(CustomExtractorInterface::class);
        $custom->expects(self::once())->method('supports')->with(\stdClass::class)->willReturn(false);
        $custom->expects(self::never())->method('extract');

        $repository = $this->createMock(ObjectRepository::class);
        $repository->method('findOneBy')->with(['id' => 5])->willReturn($object);

        $manager = $this->createMock(ObjectManager::class);
        $manager->method('getRepository')->with(\stdClass::class)->willReturn($repository);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->with(\stdClass::class)->willReturn($manager);

        $factory = $this->createMock(ModelFactoryInterface::class);
        $factory->method('supports')->with(\stdClass::class)->willReturn(true);
        $factory->method('create')->with($object)->willReturn($model);

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer->method('serialize')->with($model, 'json')->willReturn('{"ok":true}');

        $extractor = new GenericExtractor(
            $registry,
            [$factory],
            $serializer,
            $this->makeAccessCheckers(true),
            [$custom],
        );

        self::assertSame('{"ok":true}', $extractor->extract(\stdClass::class, ['id' => 5]));
    }

    private function makeAccessCheckers(bool $granted): EntityAccessCheckerRegistry
    {
        $checker = $this->createMock(EntityAccessCheckerInterface::class);
        $checker->method('supports')->willReturn(true);
        $checker->method('isGranted')->willReturn($granted);

        return new EntityAccessCheckerRegistry([$checker]);
    }
}
