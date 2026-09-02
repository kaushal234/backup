<?php

declare(strict_types=1);

namespace App\Tests\Jira\DataTransformer;

use App\Jira\DataTransformer\TextToDocumentField;
use PHPUnit\Framework\TestCase;

class TextToDocumentFieldTest extends TestCase
{
    public function testTransformer()
    {
        self::assertSame(['testField' => [
            'content' => [
                [
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => 'value',
                        ],
                    ],
                    'type' => 'paragraph',
                ],
            ],
            'type' => 'doc',
            'version' => 1,
        ]], (new TextToDocumentField())('value', ['field' => 'testField']));
    }
}
