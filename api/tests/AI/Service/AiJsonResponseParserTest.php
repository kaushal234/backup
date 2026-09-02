<?php

declare(strict_types=1);

namespace App\Tests\AI\Service;

use App\AI\Service\AiJsonResponseParser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

final class AiJsonResponseParserTest extends TestCase
{
    public function testItParsesValidJson(): void
    {
        $result = (new AiJsonResponseParser())->parse('{"foo": "bar", "n": 1}');

        self::assertSame(['foo' => 'bar', 'n' => 1], $result);
    }

    public function testItStripsJsonMarkdownFences(): void
    {
        $raw = "```json\n{\"foo\": \"bar\"}\n```";

        $result = (new AiJsonResponseParser())->parse($raw);

        self::assertSame(['foo' => 'bar'], $result);
    }

    public function testItStripsBareMarkdownFences(): void
    {
        $raw = "```\n{\"foo\": \"bar\"}\n```";

        $result = (new AiJsonResponseParser())->parse($raw);

        self::assertSame(['foo' => 'bar'], $result);
    }

    public function testItTrimsSurroundingWhitespace(): void
    {
        $result = (new AiJsonResponseParser())->parse("   \n{\"foo\":\"bar\"}\n   ");

        self::assertSame(['foo' => 'bar'], $result);
    }

    public function testItThrowsOnInvalidJson(): void
    {
        $this->expectException(ServiceUnavailableHttpException::class);
        $this->expectExceptionMessage('Invalid AI JSON response');

        (new AiJsonResponseParser())->parse('not valid json');
    }

    public function testItThrowsWhenJsonDecodesToScalar(): void
    {
        $this->expectException(ServiceUnavailableHttpException::class);

        (new AiJsonResponseParser())->parse('"a string"');
    }

    public function testItThrowsWhenJsonDecodesToNull(): void
    {
        $this->expectException(ServiceUnavailableHttpException::class);

        (new AiJsonResponseParser())->parse('null');
    }
}
