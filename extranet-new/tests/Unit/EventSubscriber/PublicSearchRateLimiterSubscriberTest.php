<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\PublicSearchRateLimiterSubscriber;
use App\Http\Responder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\InMemoryStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * @group unit
 */
final class PublicSearchRateLimiterSubscriberTest extends TestCase
{
    private const LIMIT = 3;

    private Environment&MockObject $twig;
    private LoggerInterface&MockObject $logger;
    private KernelInterface&MockObject $kernel;
    private PublicSearchRateLimiterSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->kernel = $this->createMock(KernelInterface::class);

        // Responder is final: instantiate it for real with a mocked Twig.
        $responder = new Responder(
            $this->twig,
            $this->createMock(UrlGeneratorInterface::class),
            new RequestStack(),
        );

        $factory = new RateLimiterFactory(
            [
                'id' => 'public_equipment_search',
                'policy' => 'sliding_window',
                'limit' => self::LIMIT,
                'interval' => '1 minute',
            ],
            new InMemoryStorage(),
        );

        $this->subscriber = new PublicSearchRateLimiterSubscriber($factory, $responder, $this->logger);
    }

    public function testRequestUnderLimitIsNotBlocked(): void
    {
        $this->twig->expects($this->never())->method('render');
        $this->logger->expects($this->never())->method('warning');

        for ($i = 0; $i < self::LIMIT; ++$i) {
            $event = $this->createPublicSearchEvent();
            $this->subscriber->onKernelRequest($event);

            self::assertNull($event->getResponse());
        }
    }

    public function testRequestOverLimitIsBlockedWithRetryAfter(): void
    {
        $this->twig
            ->expects($this->once())
            ->method('render')
            ->with('errors/rate_limited.html.twig', $this->arrayHasKey('retryAfter'))
            ->willReturn('rate limited')
        ;

        $this->logger->expects($this->once())->method('warning');

        // Consume the allowed quota then one extra request.
        for ($i = 0; $i < self::LIMIT; ++$i) {
            $this->subscriber->onKernelRequest($this->createPublicSearchEvent());
        }

        $blockedEvent = $this->createPublicSearchEvent();
        $this->subscriber->onKernelRequest($blockedEvent);

        $response = $blockedEvent->getResponse();
        self::assertNotNull($response);
        self::assertSame(Response::HTTP_TOO_MANY_REQUESTS, $response->getStatusCode());
        self::assertTrue($response->headers->has('Retry-After'));
    }

    public function testOtherRouteIsNeverRateLimited(): void
    {
        $this->twig->expects($this->never())->method('render');
        $this->logger->expects($this->never())->method('warning');

        for ($i = 0; $i < self::LIMIT * 3; ++$i) {
            $event = $this->createEvent('some:other:route');
            $this->subscriber->onKernelRequest($event);

            self::assertNull($event->getResponse());
        }
    }

    private function createPublicSearchEvent(): RequestEvent
    {
        return $this->createEvent('equipment:show:public');
    }

    private function createEvent(string $route): RequestEvent
    {
        $request = Request::create('/public/SN123', server: ['REMOTE_ADDR' => '203.0.113.1']);
        $request->attributes->set('_route', $route);

        return new RequestEvent($this->kernel, $request, HttpKernelInterface::MAIN_REQUEST);
    }
}
