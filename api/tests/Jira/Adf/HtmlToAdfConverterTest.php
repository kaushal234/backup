<?php

declare(strict_types=1);

namespace App\Tests\Jira\Adf;

use App\Jira\Adf\HtmlToAdfConverter;
use PHPUnit\Framework\TestCase;

class HtmlToAdfConverterTest extends TestCase
{
    private HtmlToAdfConverter $converter;

    protected function setUp(): void
    {
        $this->converter = new HtmlToAdfConverter();
    }

    public function testPlainParagraph(): void
    {
        self::assertSame(
            [
                'content' => [
                    ['content' => [['type' => 'text', 'text' => 'Hello world']], 'type' => 'paragraph'],
                ],
                'type' => 'doc',
                'version' => 1,
            ],
            $this->converter->convert('<p>Hello world</p>')
        );
    }

    public function testBareTextIsWrappedInParagraph(): void
    {
        self::assertSame(
            [
                'content' => [
                    ['content' => [['type' => 'text', 'text' => 'Hello world']], 'type' => 'paragraph'],
                ],
                'type' => 'doc',
                'version' => 1,
            ],
            $this->converter->convert('Hello world')
        );
    }

    public function testEmptyMessageProducesEmptyParagraph(): void
    {
        self::assertSame(
            ['content' => [['content' => [], 'type' => 'paragraph']], 'type' => 'doc', 'version' => 1],
            $this->converter->convert('')
        );
    }

    public function testMarks(): void
    {
        $result = $this->converter->convert('<p><strong>bold</strong> <em>italic</em> <u>under</u></p>');

        self::assertSame(
            [
                ['type' => 'text', 'text' => 'bold', 'marks' => [['type' => 'strong']]],
                ['type' => 'text', 'text' => ' '],
                ['type' => 'text', 'text' => 'italic', 'marks' => [['type' => 'em']]],
                ['type' => 'text', 'text' => ' '],
                ['type' => 'text', 'text' => 'under', 'marks' => [['type' => 'underline']]],
            ],
            $result['content'][0]['content']
        );
    }

    public function testNestedMarks(): void
    {
        $result = $this->converter->convert('<p><strong><em>both</em></strong></p>');

        self::assertSame(
            [['type' => 'text', 'text' => 'both', 'marks' => [['type' => 'strong'], ['type' => 'em']]]],
            $result['content'][0]['content']
        );
    }

    public function testLink(): void
    {
        $result = $this->converter->convert('<p><a href="https://example.com">click</a></p>');

        self::assertSame(
            [['type' => 'text', 'text' => 'click', 'marks' => [['type' => 'link', 'attrs' => ['href' => 'https://example.com']]]]],
            $result['content'][0]['content']
        );
    }

    public function testHardBreak(): void
    {
        $result = $this->converter->convert('<p>line1<br>line2</p>');

        self::assertSame(
            [
                ['type' => 'text', 'text' => 'line1'],
                ['type' => 'hardBreak'],
                ['type' => 'text', 'text' => 'line2'],
            ],
            $result['content'][0]['content']
        );
    }

    public function testBulletList(): void
    {
        $result = $this->converter->convert('<ul><li>one</li><li>two</li></ul>');

        self::assertSame(
            [
                'type' => 'bulletList',
                'content' => [
                    ['type' => 'listItem', 'content' => [['content' => [['type' => 'text', 'text' => 'one']], 'type' => 'paragraph']]],
                    ['type' => 'listItem', 'content' => [['content' => [['type' => 'text', 'text' => 'two']], 'type' => 'paragraph']]],
                ],
            ],
            $result['content'][0]
        );
    }

    public function testOrderedListWithNestedList(): void
    {
        $result = $this->converter->convert('<ol><li>outer<ul><li>inner</li></ul></li></ol>');

        self::assertSame(
            [
                'type' => 'orderedList',
                'content' => [
                    [
                        'type' => 'listItem',
                        'content' => [
                            ['content' => [['type' => 'text', 'text' => 'outer']], 'type' => 'paragraph'],
                            ['type' => 'bulletList', 'content' => [
                                ['type' => 'listItem', 'content' => [['content' => [['type' => 'text', 'text' => 'inner']], 'type' => 'paragraph']]],
                            ]],
                        ],
                    ],
                ],
            ],
            $result['content'][0]
        );
    }

    public function testHeadings(): void
    {
        $result = $this->converter->convert('<h1>Title</h1><h3>Subtitle</h3>');

        self::assertSame(
            [
                ['type' => 'heading', 'attrs' => ['level' => 1], 'content' => [['type' => 'text', 'text' => 'Title']]],
                ['type' => 'heading', 'attrs' => ['level' => 3], 'content' => [['type' => 'text', 'text' => 'Subtitle']]],
            ],
            $result['content']
        );
    }

    public function testBlockquote(): void
    {
        $result = $this->converter->convert('<blockquote><p>quoted</p></blockquote>');

        self::assertSame(
            ['type' => 'blockquote', 'content' => [['content' => [['type' => 'text', 'text' => 'quoted']], 'type' => 'paragraph']]],
            $result['content'][0]
        );
    }

    public function testUnknownTagIsTransparent(): void
    {
        $result = $this->converter->convert('<p><span>hello</span></p>');

        self::assertSame(
            [['type' => 'text', 'text' => 'hello']],
            $result['content'][0]['content']
        );
    }

    public function testCodeTagIsTransparentNotAMark(): void
    {
        $result = $this->converter->convert('<p><code>hello</code></p>');

        self::assertSame(
            [['type' => 'text', 'text' => 'hello']],
            $result['content'][0]['content']
        );
    }

    public function testMultipleParagraphsAndWhitespaceBetweenBlocksIsDropped(): void
    {
        $result = $this->converter->convert("<p>first</p>\n<p>second</p>");

        self::assertSame(
            [
                ['content' => [['type' => 'text', 'text' => 'first']], 'type' => 'paragraph'],
                ['content' => [['type' => 'text', 'text' => 'second']], 'type' => 'paragraph'],
            ],
            $result['content']
        );
    }
}
