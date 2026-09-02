<?php

declare(strict_types=1);

namespace Tests\ApiBundle\EventListener;

use ApiBundle\EventListener\ClientExceptionListener;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ClientExceptionListenerTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
    }

    public function testServiceIsInContainer()
    {
        self::assertTrue(static::getContainer()->has(ClientExceptionListener::class));
    }

    /**
     * @dataProvider handledStatusesProvider
     */
    public function testThatClientExceptionsAreCaught($status)
    {
        $response = static::$kernel->handle(Request::create("/public/client/$status"));
        self::assertSame($status, $response->getStatusCode());
    }

    public function handledStatusesProvider()
    {
        yield [Response::HTTP_NOT_FOUND];
        yield [Response::HTTP_UNPROCESSABLE_ENTITY];
    }

    /**
     * @dataProvider handledStatusesProvider
     */
    public function testAccessDeniedClientExceptions()
    {
        $this->expectException(AccessDeniedException::class);
        static::$kernel->handle(Request::create('/public/client/403'));
    }

    /**
     * @dataProvider unhandledStatusesProvider
     */
    public function testThatOtherClientExceptionsAreNotCaught($status)
    {
        $response = static::$kernel->handle(Request::create("/public/client/$status"));
        self::assertSame(500, $response->getStatusCode());
    }

    public function unhandledStatusesProvider()
    {
        yield [Response::HTTP_GONE];
        yield [Response::HTTP_I_AM_A_TEAPOT];
        yield [Response::HTTP_BAD_REQUEST];
        yield [Response::HTTP_BAD_GATEWAY];
    }
}
