<?php

declare(strict_types=1);

namespace App\Tests\AI\Handler;

use App\AI\Dto\Result;
use App\AI\Handler\ApiSearchSourceHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ApiSearchSourceHandlerTest extends TestCase
{
    public function testSupportsReturnsTrueForConfiguredModule(): void
    {
        $handler = $this->makeHandler(
            configuration: ['book' => ['class' => \stdClass::class, 'fields' => ['title'], 'route' => 'app_book_show']],
        );

        self::assertTrue($handler->supports('book'));
    }

    public function testSupportsReturnsFalseForMissingModule(): void
    {
        $handler = $this->makeHandler(
            configuration: ['book' => ['class' => \stdClass::class, 'fields' => ['title'], 'route' => 'app_book_show']],
        );

        self::assertFalse($handler->supports('movie'));
    }

    public function testHandleReturnsNullWhenEntityNotFound(): void
    {
        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects(self::once())->method('find')->with(42)->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(\stdClass::class)->willReturn($repository);

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $propertyAccessor->expects(self::never())->method('getValue');

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects(self::never())->method('generate');

        $handler = $this->makeHandler(
            $entityManager,
            $propertyAccessor,
            $urlGenerator,
            configuration: ['book' => ['class' => \stdClass::class, 'route' => 'app_book_show', 'fields' => ['title']]],
        );

        $result = $handler->handle(['src' => 'BOOK', 'id' => 42]);

        self::assertNull($result);
    }

    public function testHandleBuildsDescriptionAndGeneratesLink(): void
    {
        $source = new \stdClass();

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->method('find')->with(42)->willReturn($source);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(\stdClass::class)->willReturn($repository);

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $propertyAccessor->method('getValue')->willReturnMap([
            [$source, 'title', 'The Hobbit'],
            [$source, 'author', 'Tolkien'],
        ]);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->with('app_book_show', ['id' => 42])
            ->willReturn('/books/42');

        $handler = $this->makeHandler(
            $entityManager,
            $propertyAccessor,
            $urlGenerator,
            configuration: ['book' => ['class' => \stdClass::class, 'route' => 'app_book_show', 'fields' => ['title', 'author']]],
        );

        $result = $handler->handle(['src' => 'BOOK', 'id' => 42]);

        self::assertInstanceOf(Result::class, $result);
        self::assertSame(42, $result->id);
        self::assertSame('BOOK', $result->module);
        self::assertSame('The Hobbit Tolkien', $result->description);
        self::assertSame('/books/42', $result->link);
    }

    public function testHandleLogsErrorWhenFieldCannotBeReadAndContinues(): void
    {
        $source = new \stdClass();
        $exception = new \RuntimeException('Field "missingField" does not exist.');

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->method('find')->willReturn($source);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $propertyAccessor = $this->createMock(PropertyAccessorInterface::class);
        $propertyAccessor->method('getValue')->willReturnCallback(
            static function (object $obj, string $field) use ($exception): string {
                if ('missingField' === $field) {
                    throw $exception;
                }

                return match ($field) {
                    'title' => 'The Hobbit',
                    'author' => 'Tolkien',
                    default => '',
                };
            }
        );

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/books/42');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error')->with(
            'Could not get description matching AI configuration. Reason : {reason}',
            ['reason' => 'Field "missingField" does not exist.'],
        );

        $handler = $this->makeHandler(
            $entityManager,
            $propertyAccessor,
            $urlGenerator,
            $logger,
            configuration: ['book' => ['class' => \stdClass::class, 'route' => 'app_book_show', 'fields' => ['title', 'missingField', 'author']]],
        );

        $result = $handler->handle(['src' => 'BOOK', 'id' => 42]);

        self::assertInstanceOf(Result::class, $result);
        self::assertSame('The Hobbit Tolkien', $result->description);
    }

    /**
     * @param array<string, array{class: class-string, fields: list<string>, route: string}> $configuration
     */
    private function makeHandler(
        ?EntityManagerInterface $entityManager = null,
        ?PropertyAccessorInterface $propertyAccessor = null,
        ?UrlGeneratorInterface $urlGenerator = null,
        ?LoggerInterface $logger = null,
        array $configuration = [],
    ): ApiSearchSourceHandler {
        return new ApiSearchSourceHandler(
            $entityManager ?? $this->createMock(EntityManagerInterface::class),
            $propertyAccessor ?? $this->createMock(PropertyAccessorInterface::class),
            $urlGenerator ?? $this->createMock(UrlGeneratorInterface::class),
            $logger ?? $this->createMock(LoggerInterface::class),
            $configuration,
        );
    }
}
