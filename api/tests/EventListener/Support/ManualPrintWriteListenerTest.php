<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Support;

use App\Entity\Support\Manual;
use App\Entity\Support\ManualPrint;
use App\EventListener\Support\ManualPrintWriteListener;
use App\Notifier\Support\ManualPrinterNotifier;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ManualPrintWriteListenerTest extends KernelTestCase
{
    use ProphecyTrait;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    /**
     * @dataProvider printerEventProvider
     */
    public function testAnEmailIsSendToThePrinter(object $controllerResult, bool $isPostMethod, int $expectedCall): void
    {
        $notifierProphecy = $this->prophesize(ManualPrinterNotifier::class);
        $notifierProphecy->sendEmail(Argument::type(ManualPrint::class))->shouldBeCalledTimes($expectedCall);

        $serviceLocatorProphecy = $this->prophesize(ContainerInterface::class);
        $serviceLocatorProphecy->get(ManualPrinterNotifier::class)->shouldBeCalledTimes($expectedCall)->willReturn($notifierProphecy->reveal());

        $requestProphecy = $this->prophesize(Request::class);
        $requestProphecy->isMethod(Request::METHOD_POST)->willReturn($isPostMethod);

        $writeListener = new ManualPrintWriteListener($serviceLocatorProphecy->reveal());
        $writeListener->generateZipAndSendRequestToPrinter(new ViewEvent(static::$kernel, $requestProphecy->reveal(), HttpKernelInterface::MAIN_REQUEST, $controllerResult));
    }

    public function printerEventProvider()
    {
        $manualPrint = new ManualPrint();
        $manualPrint->manual = new Manual();

        yield 'Email sent' => [$manualPrint, true, 1];
        yield 'Not a POST method' => [$manualPrint, false, 0];
        yield 'Not a printer' => [new \stdClass(), true, 0];
        yield 'Not a POST method and not a printer' => [new \stdClass(), false, 0];
    }
}
