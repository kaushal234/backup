<?php

declare(strict_types=1);

namespace App\Tests\AI\Builder;

use App\AI\Builder\UserMessageBuilder;
use App\AI\Service\FileTextExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Message\Content\DocumentUrl;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\UserMessage;

final class UserMessageBuilderTest extends TestCase
{
    public function testBuildWithTextOnly(): void
    {
        $builder = new UserMessageBuilder($this->extractorReturning(null));

        $message = $builder->build('Hello world');

        self::assertInstanceOf(UserMessage::class, $message);

        $contents = $message->getContent();
        self::assertCount(1, $contents);
        self::assertInstanceOf(Text::class, $contents[0]);
        self::assertSame('Hello world', $contents[0]->getText());
    }

    public function testBuildWithTextExtractableFileEmbedsExtractedText(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.pdf';
        file_put_contents($tempFile, 'irrelevant binary content');

        try {
            $builder = new UserMessageBuilder($this->extractorReturning('Extracted plain text from the document.'));

            $message = $builder->build('Summarize this', $tempFile);

            $contents = $message->getContent();
            self::assertCount(2, $contents);
            self::assertInstanceOf(Text::class, $contents[0]);
            self::assertSame('Summarize this', $contents[0]->getText());
            self::assertInstanceOf(Text::class, $contents[1]);
            self::assertStringContainsString('Extracted plain text from the document.', $contents[1]->getText());
        } finally {
            @unlink($tempFile);
        }
    }

    public function testBuildWithNonExtractableFileFallsBackToDocumentUrl(): void
    {
        $fileContent = 'fake-image-bytes';
        $tempFile = tempnam(sys_get_temp_dir(), 'test_').'.png';
        file_put_contents($tempFile, $fileContent);

        try {
            $builder = new UserMessageBuilder($this->extractorReturning(null));

            $message = $builder->build('Look at this', $tempFile);

            $contents = $message->getContent();
            self::assertCount(2, $contents);
            self::assertInstanceOf(DocumentUrl::class, $contents[1]);
            self::assertStringStartsWith('data:', $contents[1]->getUrl());
            self::assertStringContainsString(base64_encode($fileContent), $contents[1]->getUrl());
        } finally {
            @unlink($tempFile);
        }
    }

    public function testExtractPromptReturnsFirstTextContent(): void
    {
        $message = new UserMessage(
            new Text('Original prompt'),
            new Text('[Attached file content] extracted body'),
        );

        self::assertSame('Original prompt', UserMessageBuilder::extractPrompt($message));
    }

    public function testExtractPromptWithoutTextContentReturnsEmptyString(): void
    {
        $message = new UserMessage(new DocumentUrl('data:application/pdf;base64,AAAA'));

        self::assertSame('', UserMessageBuilder::extractPrompt($message));
    }

    public function testBuildWithNullFilepathDoesNotIncludeDocument(): void
    {
        $builder = new UserMessageBuilder($this->extractorReturning(null));

        $message = $builder->build('Hello', null);

        $contents = $message->getContent();
        self::assertCount(1, $contents);
        self::assertInstanceOf(Text::class, $contents[0]);
    }

    private function extractorReturning(?string $extracted): FileTextExtractor
    {
        $extractor = $this->createMock(FileTextExtractor::class);
        $extractor->method('extract')->willReturn($extracted);

        return $extractor;
    }
}
