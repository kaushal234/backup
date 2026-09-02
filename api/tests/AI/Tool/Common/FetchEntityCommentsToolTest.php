<?php

declare(strict_types=1);

namespace App\Tests\AI\Tool\Common;

use App\AI\Dto\Activity\CommentModel;
use App\AI\Exception\AccessDeniedException;
use App\AI\Exception\EntityNotFoundException;
use App\AI\Security\EntityAccessCheckerInterface;
use App\AI\Security\EntityAccessCheckerRegistry;
use App\AI\Service\AIEntityRegistry;
use App\AI\Service\Loader\Comment\CommentLoader;
use App\AI\Service\Loader\Comment\LegacyCommentLoader;
use App\AI\Service\Loader\RelatedEntity\RelatedEntityLoader;
use App\AI\Tool\Common\FetchEntityCommentsTool;
use App\Entity\Sales\SalesForecast;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\SerializerInterface;

final class FetchEntityCommentsToolTest extends TestCase
{
    public function testUnknownEntityTypeThrows(): void
    {
        $tool = $this->makeTool(
            entity: null,
            commentLoader: $this->createMock(CommentLoader::class),
            granted: true,
        );

        $this->expectException(\InvalidArgumentException::class);
        $tool('unknown', 42);
    }

    public function testEntityNotFoundThrows(): void
    {
        $tool = $this->makeTool(
            entity: null,
            commentLoader: $this->createMock(CommentLoader::class),
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
            commentLoader: $this->createMock(CommentLoader::class),
            granted: false,
        );

        $this->expectException(AccessDeniedException::class);
        $tool('sales_forecast', 42);
    }

    public function testReturnsSerializedComments(): void
    {
        $entity = $this->createMock(SalesForecast::class);
        $comment = new CommentModel('hello', new \DateTimeImmutable('2025-01-02'), null, null, null);

        $commentLoader = $this->createMock(CommentLoader::class);
        $commentLoader->method('findComments')->with($entity)->willReturn([$comment]);

        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->expects(self::once())
            ->method('serialize')
            ->with([$comment], 'json')
            ->willReturn('[{"message":"hello"}]');

        $tool = $this->makeTool(
            entity: $entity,
            commentLoader: $commentLoader,
            granted: true,
            serializer: $serializer,
        );

        self::assertSame('[{"message":"hello"}]', $tool('sales_forecast', 42));
    }

    private function makeTool(
        ?object $entity,
        CommentLoader $commentLoader,
        bool $granted,
        ?SerializerInterface $serializer = null,
    ): FetchEntityCommentsTool {
        $registry = new AIEntityRegistry(
            ['sales_forecast' => ['class' => SalesForecast::class, 'comments' => ['type' => 'default', 'legacy_module' => null]]],
            $commentLoader,
            $this->createMock(LegacyCommentLoader::class),
            $this->createMock(RelatedEntityLoader::class),
        );

        return new FetchEntityCommentsTool(
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
