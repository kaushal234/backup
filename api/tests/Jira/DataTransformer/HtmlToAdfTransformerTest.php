<?php

declare(strict_types=1);

namespace App\Tests\Jira\DataTransformer;

use App\Jira\Adf\HtmlToAdfConverter;
use App\Jira\DataTransformer\HtmlToAdfTransformer;
use PHPUnit\Framework\TestCase;

class HtmlToAdfTransformerTest extends TestCase
{
    public function testTransformerConvertsHtmlToAdfUnderTheGivenField(): void
    {
        $transformer = new HtmlToAdfTransformer(new HtmlToAdfConverter());

        self::assertSame(
            ['body' => [
                'content' => [['content' => [['type' => 'text', 'text' => 'Hello world']], 'type' => 'paragraph']],
                'type' => 'doc',
                'version' => 1,
            ]],
            $transformer('<p>Hello world</p>', ['field' => 'body'])
        );
    }

    public function testTransformerReturnsNullForNullValue(): void
    {
        $transformer = new HtmlToAdfTransformer(new HtmlToAdfConverter());

        self::assertNull($transformer(null, ['field' => 'body']));
    }
}
