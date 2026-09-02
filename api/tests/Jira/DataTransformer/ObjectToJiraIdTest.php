<?php

declare(strict_types=1);

namespace App\Tests\Jira\DataTransformer;

use App\Jira\DataTransformer\ObjectToJiraId;
use App\Jira\Resources\Priority;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class ObjectToJiraIdTest extends TestCase
{
    use ProphecyTrait;

    public function testTransformer()
    {
        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $priority = new Priority();

        $propertyAccessorProphecy->getValue($priority, 'id')->shouldBeCalledOnce()->willReturn('12');

        self::assertSame(['priority' => ['id' => '12']], (new ObjectToJiraId($propertyAccessorProphecy->reveal()))($priority, ['field' => 'priority']));
    }

    public function testTransformerWhenValueIsNull()
    {
        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $priority = new Priority();

        $propertyAccessorProphecy->getValue($priority, 'id')->shouldBeCalledOnce()->willReturn(null);

        self::assertSame([], (new ObjectToJiraId($propertyAccessorProphecy->reveal()))($priority, ['field' => 'priority']));
    }
}
