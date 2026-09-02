<?php

declare(strict_types=1);

namespace App\Tests\AI\Service\Loader\Comment;

use App\AI\Service\Loader\Comment\CommentLoader;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Sales\SalesForecast;
use App\Repository\Common\CommentRepository;
use PHPUnit\Framework\TestCase;

final class CommentLoaderTest extends TestCase
{
    public function testFindCommentsMapsAndFiltersBlank(): void
    {
        $entity = $this->createMock(SalesForecast::class);

        $author = $this->createMock(People::class);
        $author->method('getEmail')->willReturn('john@example.com');
        $author->method('getFirstname')->willReturn('John');
        $author->method('getLastname')->willReturn('Doe');

        $comment = $this->createMock(Comment::class);
        $comment->method('getMessage')->willReturn('  Hello world  ');
        $comment->method('getCreatedAt')->willReturn(new \DateTimeImmutable('2025-01-02'));
        $comment->method('getUser')->willReturn($author);

        $blank = $this->createMock(Comment::class);
        $blank->method('getMessage')->willReturn('   ');

        $repo = $this->createMock(CommentRepository::class);
        $repo->method('findCommentsForEntity')->with($entity)->willReturn([$comment, $blank]);

        $loader = new CommentLoader($repo);
        $result = $loader->findComments($entity);

        self::assertCount(1, $result);
        self::assertSame('Hello world', $result[0]->message);
        self::assertSame('john@example.com', $result[0]->authorEmail);
        self::assertSame('John', $result[0]->authorFirstname);
        self::assertSame('Doe', $result[0]->authorLastname);
    }
}
