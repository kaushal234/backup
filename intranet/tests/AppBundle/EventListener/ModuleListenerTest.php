<?php

declare(strict_types=1);

namespace Tests\AppBundle\EventListener;

use App\Kernel;
use AppBundle\EventListener\ModuleListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class ModuleListenerTest extends TestCase
{
    public function testRequestAttributeCreatesAnHTTPHeader()
    {
        $listener = new ModuleListener();

        $request = new Request([], [], [], [], [], [], null);
        $request->attributes->set('alvest_module', 'MST');

        $event = new ResponseEvent(new Kernel('pouet', true), $request, 1, new Response());

        $listener->onKernelResponse($event);

        self::assertSame('MST', $event->getResponse()->headers->get('X-Alvest-Module'));
    }
}
