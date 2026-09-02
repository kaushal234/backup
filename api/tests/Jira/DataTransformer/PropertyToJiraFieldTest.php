<?php

declare(strict_types=1);

namespace App\Tests\Jira\DataTransformer;

use App\Jira\DataTransformer\PropertyToJiraField;
use PHPUnit\Framework\TestCase;

class PropertyToJiraFieldTest extends TestCase
{
    public function testTransformer()
    {
        self::assertSame(['jsdPublic' => true], (new PropertyToJiraField())(true, ['field' => 'jsdPublic']));
    }
}
