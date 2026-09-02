<?php

declare(strict_types=1);

namespace Tests\AppBundle\Twig\Components\Chat;

use ApiBundle\Client;
use ApiBundle\ClientExceptionMapper;
use AppBundle\Twig\Components\Chat\Sidebar;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SidebarTest extends TestCase
{
    use ProphecyTrait;

    public function testPinConversationSuccess(): void
    {
        $sidebar = $this->createSidebar([
            new MockResponse('{}', ['http_code' => 200]),
        ]);

        $sidebar->pinConversation(1, true);

        self::assertNull($sidebar->pinError);
    }

    public function testPinConversationSetsErrorOnValidationFailure(): void
    {
        $body = json_encode([
            '@type' => 'ConstraintViolationList',
            'violations' => [
                ['message' => 'You cannot pin more than 15 conversations.'],
            ],
        ]);

        $sidebar = $this->createSidebar([
            new MockResponse($body, [
                'http_code' => 422,
                'response_headers' => ['content-type' => 'application/json'],
            ]),
        ]);

        $sidebar->pinConversation(1, true);

        self::assertSame('You cannot pin more than 15 conversations.', $sidebar->pinError);
    }

    public function testPinConversationClearsPreviousError(): void
    {
        $sidebar = $this->createSidebar([
            new MockResponse('{}', ['http_code' => 200]),
        ]);
        $sidebar->pinError = 'previous error';

        $sidebar->pinConversation(1, true);

        self::assertNull($sidebar->pinError);
    }

    public function testDeleteConversationThrowsWhenLogNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $sidebar = $this->createSidebar([
            $this->logsResponse([]),
        ]);

        $sidebar->deleteConversation(99);
    }

    public function testDeleteConversationReturnsNullWhenNotCurrentLog(): void
    {
        $sidebar = $this->createSidebar([
            $this->logsResponse([['id' => 1]]),
            new MockResponse('', ['http_code' => 204]),
        ], currentLogId: 2);

        $result = $sidebar->deleteConversation(1);

        self::assertNull($result);
    }

    public function testDeleteConversationRedirectsWhenDeletingCurrentLog(): void
    {
        $sidebar = $this->createSidebar([
            $this->logsResponse([['id' => 1]]),
            new MockResponse('', ['http_code' => 204]),
        ], currentLogId: 1);

        $result = $sidebar->deleteConversation(1);

        self::assertInstanceOf(RedirectResponse::class, $result);
        self::assertSame('/chat', $result->getTargetUrl());
    }

    private function createSidebar(array $responses, ?int $currentLogId = null): Sidebar
    {
        $httpClient = new MockHttpClient(array_map(
            static fn (MockResponse $r) => $r,
            $responses,
        ));

        $security = $this->prophesize(Security::class);
        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $kernel = $this->prophesize(KernelInterface::class);
        $kernel->getCacheDir()->willReturn('');
        $kernel->isDebug()->willReturn(false);

        $client = new Client(
            $security->reveal(),
            $eventDispatcher->reveal(),
            $kernel->reveal(),
            $httpClient,
            ['base_uri' => 'https://haproxy:8080'],
        );

        $urlGenerator = $this->prophesize(UrlGeneratorInterface::class);
        $urlGenerator->generate('chat_home')->willReturn('/chat');

        $sidebar = new Sidebar($client, new ClientExceptionMapper(), $urlGenerator->reveal());
        $sidebar->currentLogId = $currentLogId;

        return $sidebar;
    }

    private function logsResponse(array $logs = []): MockResponse
    {
        return new MockResponse(
            json_encode(['hydra:member' => $logs, 'hydra:totalItems' => \count($logs)]),
            ['response_headers' => ['content-type' => 'application/ld+json']],
        );
    }
}
