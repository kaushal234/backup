<?php

declare(strict_types=1);

namespace App\Tests\Unit\Monolog\Processor;

use App\Monolog\Processor\TruncateCriticalAlertProcessor;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

class TruncateCriticalAlertProcessorTest extends TestCase
{
    private const MAX_LENGTH = 8000;

    private TruncateCriticalAlertProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new TruncateCriticalAlertProcessor();
    }

    public function testNormalSizedRecordIsReturnedUnchanged(): void
    {
        $record = $this->createRecord('Something went wrong', ['exception' => new \RuntimeException('short message')]);

        $processed = ($this->processor)($record);

        $this->assertSame($record, $processed);
    }

    public function testRecordWithoutExceptionContextIsReturnedUnchanged(): void
    {
        $record = $this->createRecord('Something went wrong', ['foo' => 'bar']);

        $processed = ($this->processor)($record);

        $this->assertSame($record, $processed);
    }

    public function testOversizedMessageIsTruncated(): void
    {
        $record = $this->createRecord(str_repeat('a', self::MAX_LENGTH + 1000));

        $processed = ($this->processor)($record);

        $this->assertNotSame($record, $processed);
        $this->assertSame(self::MAX_LENGTH + mb_strlen('… [truncated]'), mb_strlen($processed->message));
        $this->assertStringEndsWith('… [truncated]', $processed->message);
        $this->assertSame($record->context, $processed->context);
    }

    public function testOversizedExceptionMessageIsSummarizedAndTruncated(): void
    {
        $exception = new \RuntimeException(str_repeat('b', self::MAX_LENGTH + 1000), 42);
        $record = $this->createRecord('Something went wrong', ['exception' => $exception]);

        $processed = ($this->processor)($record);

        $this->assertNotSame($record, $processed);
        $this->assertSame('Something went wrong', $processed->message);

        $summary = $processed->context['exception'];
        $this->assertIsArray($summary);
        $this->assertSame(\RuntimeException::class, $summary['class']);
        $this->assertSame(42, $summary['code']);
        $this->assertStringEndsWith('… [truncated]', $summary['message']);
        $this->assertSame(self::MAX_LENGTH + mb_strlen('… [truncated]'), mb_strlen($summary['message']));
        $this->assertSame(\sprintf('%s:%d', $exception->getFile(), $exception->getLine()), $summary['file']);
        $this->assertArrayNotHasKey('previous', $summary);
    }

    public function testOversizedExceptionSummarizesNestedPreviousExceptions(): void
    {
        $previous = new \LogicException(str_repeat('c', self::MAX_LENGTH + 1000));
        $exception = new \RuntimeException('outer message', 0, $previous);
        $record = $this->createRecord('Something went wrong', ['exception' => $exception]);

        $processed = ($this->processor)($record);

        $summary = $processed->context['exception'];
        $this->assertSame('outer message', $summary['message']);
        $this->assertArrayHasKey('previous', $summary);
        $this->assertSame(\LogicException::class, $summary['previous']['class']);
        $this->assertStringEndsWith('… [truncated]', $summary['previous']['message']);
    }

    public function testOnlyExceptionOverThresholdIsSummarizedWhenMessageIsNormal(): void
    {
        $exception = new \RuntimeException(str_repeat('d', self::MAX_LENGTH + 1));
        $record = $this->createRecord('normal message', ['exception' => $exception]);

        $processed = ($this->processor)($record);

        $this->assertSame('normal message', $processed->message);
        $this->assertIsArray($processed->context['exception']);
    }

    private function createRecord(string $message, array $context = []): LogRecord
    {
        return new LogRecord(
            new \DateTimeImmutable(),
            'app',
            Level::Critical,
            $message,
            $context,
        );
    }
}
