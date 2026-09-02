<?php

declare(strict_types=1);

namespace App\Tests\Jira\DataTransformer;

use App\Jira\DataTransformer\PropertyToCustomField;
use PHPUnit\Framework\TestCase;

class PropertyToCustomFieldTest extends TestCase
{
    public function testTransformer()
    {
        self::assertSame(['customField' => 'value'], (new PropertyToCustomField())('value', ['field' => 'customField']));
    }
}
