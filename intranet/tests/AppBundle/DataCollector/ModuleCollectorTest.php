<?php

declare(strict_types=1);

namespace Tests\AppBundle\DataCollector;

use AppBundle\DataCollector\ModuleCollector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuleCollectorTest extends TestCase
{
    public function testCollectorCollectsModule()
    {
        $collector = new ModuleCollector();

        self::assertNull($collector->getModule());

        $request = new Request([], [], [], [], [], [], null);
        $request->attributes->set('alvest_module', 'MST');

        $collector->collect($request, new Response());

        self::assertSame('MST', $collector->getModule());
    }
}
