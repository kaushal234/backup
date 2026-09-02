<?php

declare(strict_types=1);

namespace App\Tests\Jira\DataTransformer;

use App\Jira\DataTransformer\ProductToCustomField;
use App\Jira\Enum\TracteasyCustomField;
use PHPUnit\Framework\TestCase;

class ProductToCustomFieldTest extends TestCase
{
    public function testTransformerWithEzTow()
    {
        self::assertSame(
            ['customfield_10112' => ['id' => '10074']],
            (new ProductToCustomField())('EZTow', ['field' => TracteasyCustomField::Product])
        );
    }

    public function testTransformerWithEzDolly()
    {
        self::assertSame(
            ['customfield_10112' => ['id' => '10075']],
            (new ProductToCustomField())('EZ Dolly', ['field' => TracteasyCustomField::Product])
        );
    }

    public function testTransformerThrowsWhenProductIsUnmapped()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ProductToCustomField())('Unknown Product', ['field' => TracteasyCustomField::Product]);
    }

    public function testTransformerThrowsWhenValueIsNotAString()
    {
        $this->expectException(\InvalidArgumentException::class);

        (new ProductToCustomField())(null, ['field' => TracteasyCustomField::Product]);
    }
}
