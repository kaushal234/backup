<?php

declare(strict_types=1);

namespace App\Monolog\Processor;

use Monolog\LogRecord;

/**
 * The 'symfony_critical' handler emails the raw log message and exception
 * as-is (subject + HTML body). Some exceptions embed unbounded payloads
 * (e.g. base64-encoded files, full request bodies), which produces emails
 * with multi-hundred-KB subjects/bodies that SMTP servers reject, so the
 * alert never reaches anyone and the SendEmailMessage piles up in the
 * messenger 'failed' transport instead.
 *
 * Wired onto the 'symfony_critical' handler via config/packages/monolog.yaml
 * (when@prod) rather than #[AsMonologProcessor], since that handler only
 * exists in the prod environment and the attribute has no env condition.
 */
final class TruncateCriticalAlertProcessor
{
    private const MAX_LENGTH = 8000;

    public function __invoke(LogRecord $record): LogRecord
    {
        $exception = $record->context['exception'] ?? null;
        $exception = $exception instanceof \Throwable ? $exception : null;
        $messageTooLong = mb_strlen($record->message) > self::MAX_LENGTH;
        $exceptionTooLong = $this->exceedsThreshold($exception);

        if (!$messageTooLong && !$exceptionTooLong) {
            return $record;
        }

        $context = $record->context;
        if ($exceptionTooLong && $exception instanceof \Throwable) {
            $context['exception'] = $this->summarizeException($exception);
        }

        return $record->with(
            message: $messageTooLong ? $this->truncate($record->message) : $record->message,
            context: $context,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function summarizeException(\Throwable $exception): array
    {
        $data = [
            'class' => $exception::class,
            'message' => $this->truncate($exception->getMessage()),
            'code' => $exception->getCode(),
            'file' => \sprintf('%s:%d', $exception->getFile(), $exception->getLine()),
            'trace' => array_map(
                static fn (array $frame): string => isset($frame['file'], $frame['line']) ? \sprintf('%s:%d', $frame['file'], $frame['line']) : '{internal}',
                $exception->getTrace(),
            ),
        ];

        $previous = $exception->getPrevious();
        if ($previous instanceof \Throwable) {
            $data['previous'] = $this->summarizeException($previous);
        }

        return $data;
    }

    private function truncate(string $text): string
    {
        return mb_strlen($text) > self::MAX_LENGTH ? mb_substr($text, 0, self::MAX_LENGTH).'… [truncated]' : $text;
    }

    private function exceedsThreshold(?\Throwable $exception): bool
    {
        if (!$exception instanceof \Throwable) {
            return false;
        }

        return mb_strlen($exception->getMessage()) > self::MAX_LENGTH || $this->exceedsThreshold($exception->getPrevious());
    }
}
