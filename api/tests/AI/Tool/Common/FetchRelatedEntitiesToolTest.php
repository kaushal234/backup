<?php

declare(strict_types=1);

namespace App\Tests\AI\Tool\Common;

use App\AI\Dto\Activity\RelatedEntityModel;
use App\AI\Exception\AccessDeniedException;
use App\AI\Exception\EntityNotFoundException;
use App\AI\Security\EntityAccessCheckerInterface;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\AI\Service\AIEntityRegistry;
use App\AI\Service\Loader\Comment\CommentLoader;
use App\AI\Service\Loader\Comment\LegacyCommentLoader;
use App\AI\Service\Loader\RelatedEntity\RelatedEntityLoader;
use App\AI\Tool\Common\FetchRelatedEntitiesTool;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

final class FetchRelatedEntitiesToolTest extends TestCase
{
    public function testUnknownEntityTypeThrows(): void
    {
        $tool = $this->makeTool(
            entity: null,
            relatedEntityLoader: $this->createMock(RelatedEntityLoader::class),
            granted: true,
        );

        $this->expectException(\InvalidArgumentException::class);
        $tool('unknown', 42);
    }

    public function testEntityNotFoundThrows(): void
    {
        $tool = $this->makeTool(
            entity: null,
            relatedEntityLoader: $this->createMock(RelatedEntityLoader::class),
            granted: true,
        );

        $this->expectException(EntityNotFoundException::class);
        $tool('sales_forecast', 42);
    }

    public function testAccessDeniedThrows(): void
    {
        $entity = $this->createMock(SalesForecast::class);

        $tool = $this->makeTool(
            entity: $entity,
            relatedEntityLoader: $this->createMock(RelatedEntityLoader::class),
            granted: false,
        );

        $this->expectException(AccessDeniedException::class);
        $tool('sales_forecast', 42);
    }

    public function testReturnsSerializedRelatedEntities(): void
    {
        $entity = $this->createMock(SalesForecast::class);
        $related = new RelatedEntityModel('CRAB', 999);

        $relatedEntityLoader = $this->createMock(RelatedEntityLoader::class);
        $relatedEntityLoader->method('findRelatedEntities')->with($entity, 'legacy_id', 'SFR')->willReturn([$related]);

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->expects(self::once())
            ->method('serialize')
            ->with([$related], 'json')
            ->willReturn('[{"type":"CRAB"}]');

        $tool = $this->makeTool(
            entity: $entity,
            relatedEntityLoader: $relatedEntityLoader,
            granted: true,
            serializer: $serializer,
        );

        self::assertSame('[{"type":"CRAB"}]', $tool('sales_forecast', 42));
    }

    private function makeTool(
        ?object $entity,
        RelatedEntityLoader $relatedEntityLoader,
        bool $granted,
        ?SerializerInterface $serializer = null,
    ): FetchRelatedEntitiesTool {
        $registry = new AIEntityRegistry(
            ['sales_forecast' => [
                'class' => SalesForecast::class,
                'related_entities' => ['parent_id' => 'legacy_id', 'module' => 'SFR'],
            ]],
            $this->createMock(CommentLoader::class),
            $this->createMock(LegacyCommentLoader::class),
            $relatedEntityLoader,
        );

        return new FetchRelatedEntitiesTool(
            $this->makeManagerRegistry($entity),
            $serializer ?? $this->createMock(SerializerInterface::class),
            $this->makeAccessCheckers($granted),
            $registry,
        );
    }

    private function makeAccessCheckers(bool $granted): EntityAccessCheckerRegistry
    {
        $checker = $this->createMock(EntityAccessCheckerInterface::class);
        $checker->method('supports')->willReturn(true);
        $checker->method('isGranted')->willReturn($granted);

        return new EntityAccessCheckerRegistry([$checker]);
    }

    private function makeManagerRegistry(?object $entity): ManagerRegistry
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('findOneBy')->willReturn($entity);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($repository);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        return $registry;
    }
}
