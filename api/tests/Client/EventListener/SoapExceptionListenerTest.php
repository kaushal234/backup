<?php

declare(strict_types=1);

namespace App\Tests\Client\EventListener;

use App\Client\EventListener\SoapExceptionListener;
use App\Client\Exception\SoapException;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class SoapExceptionListenerTest extends TestCase
{
    use ProphecyTrait;

    public function testNotFoundSoapFaultThrowsANotFoundException()
    {
        $event = $this->executeListener(...[
            (object) ['messageType' => 'troisieme', 'messageText' => 'paul'],
            (object) ['messageType' => 'Error', 'messageText' => 'Object not found.'],
        ]);

        $this->assertInstanceOf(NotFoundHttpException::class, $event->getThrowable());
        $this->assertInstanceOf(SoapException::class, $event->getThrowable()->getPrevious());
        $this->assertSame('The resource CouldNotBeFound could not be found', $event->getThrowable()->getMessage());
    }

    public function testNormalSoapFaultThrowsABadRequestException()
    {
        $event = $this->executeListener(...[
            (object) ['messageType' => 'troisieme', 'messageText' => 'paul'],
            (object) ['messageType' => 'Error', 'messageText' => 'Objecto esta no fouñado.'],
            (object) ['messageType' => 'Error', 'messageText' => 'error between chair and keyboard'],
            (object) ['messageType' => 'plop', 'messageText' => 'not an error type, so not in combined messages'],
        ]);

        $this->assertInstanceOf(BadRequestHttpException::class, $event->getThrowable());
        $this->assertInstanceOf(SoapException::class, $event->getThrowable()->getPrevious());
        $this->assertSame('Generic error message (Generic error message / Objecto esta no fouñado. / error between chair and keyboard)', $event->getThrowable()->getMessage());
    }

    private function executeListener(\stdClass ...$messages): ExceptionEvent
    {
        $timekeepingRequestLogger = $this->prophesize(LoggerInterface::class);
        $listener = new SoapExceptionListener($timekeepingRequestLogger->reveal());

        $detail = (object) [
            'Result' => (object) [
                'messageText' => 'Generic error message',
                'MessageDetails' => (object) [
                    'Message' => (object) $messages,
                ],
            ],
        ];

        $exception = SoapException::createFromSoapFault('CouldNotBeFound', new \SoapFault('quantum', 'error', 'Scott Bakula', $detail));

        $listener->onSoapException($event = new ExceptionEvent(
            $this->prophesize(HttpKernelInterface::class)->reveal(),
            $this->prophesize(Request::class)->reveal(),
            42,
            $exception
        ));

        return $event;
    }
}
