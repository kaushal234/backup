<?php

declare(strict_types=1);

namespace Tests\AppBundle\Twig\Extension;

use AppBundle\Twig\Extension\TicketMetaDataExtension;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TicketMetaDataExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testTicketDataAreHarvestedFromRequest()
    {
        $request = new Request();

        $request->server->set('REQUEST_URI', '/caribous');
        $request->headers->set('referer', 'https://www.google.ca');

        $request->setMethod('POST');
        $request->request->set('key', 'value');
        $request->request->set('key_with_pass_in_it', 'P@ssw0rd');
        $request->attributes->set('alvest_module', 'MST');

        $requestStackProphecy = $this->prophesize(RequestStack::class);
        $requestStackProphecy->getCurrentRequest()->shouldBeCalledTimes(1)->willReturn($request);

        $extension = new TicketMetaDataExtension($requestStackProphecy->reveal());

        self::assertSame([
            'url' => '/caribous',
            'hostname' => gethostname(),
            'request' => '{"key":"value"}',
            'referer' => 'https://www.google.ca',
            'module' => 'MST',
        ], $extension->getTicketData());
    }
}
