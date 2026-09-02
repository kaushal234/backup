<?php

declare(strict_types=1);

namespace Tests\AppBundle\Twig\Components;

use ApiBundle\Client;
use ApiBundle\Model\User;
use ApiBundle\Security\Core\Authentication\UserProvider;
use ApiBundle\Security\JWTReader;
use AppBundle\Manager\FileManager;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\UX\LiveComponent\Test\InteractsWithLiveComponents;
use Symfony\UX\LiveComponent\Test\TestLiveComponent;

abstract class LiveComponentTestCase extends KernelTestCase
{
    use InteractsWithLiveComponents {
        createLiveComponent as private traitCreateLiveComponent;
    }

    protected KernelBrowser $browser;
    private array $apiResponses = [];
    private array $capturedApiRequests = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->capturedApiRequests = [];
        $this->browser = self::getContainer()->get('test.client');
        $this->browser->disableReboot();
        $this->stubCsrfTokenManager();
        $this->stubApiClient();
        $this->stubUserProvider();
    }

    // The trait fetches test.client from the container directly, which returns a fresh
    // (unauthenticated) browser each time. We override to inject $this->browser — the one
    // on which loginUser() was called — so authentication and response state are shared.
    protected function createLiveComponent(string $name, array $data = [], ?KernelBrowser $client = null): TestLiveComponent
    {
        return $this->traitCreateLiveComponent($name, $data, $client ?? $this->browser);
    }

    protected function login(string $role): User
    {
        $user = TestUser::from($role)->build();
        $this->browser->loginUser($user);

        return $user;
    }

    protected function mockApi(string $path, array $data, int $statusCode = 200, ?string $method = null): void
    {
        // Stored sequentially: method-specific mocks are matched before method-less ones
        // so that the same path can be mocked differently per HTTP verb (e.g. GET 200 on mount,
        // PUT 422 on save).
        $this->apiResponses[] = [
            'path' => $path,
            'method' => $method,
            'data' => $data,
            'status' => $statusCode,
        ];
    }

    protected function getLastCapturedApiRequestBody(): ?array
    {
        if (empty($this->capturedApiRequests)) {
            return null;
        }

        return end($this->capturedApiRequests)['body'] ?? null;
    }

    protected function mockFileManager(): MockObject
    {
        $mock = $this->createMock(FileManager::class);
        self::getContainer()->set(FileManager::class, $mock);

        return $mock;
    }

    private function stubCsrfTokenManager(): void
    {
        // KernelTestCase has no active HTTP request, so SessionTokenStorage throws
        // SessionNotFoundException when the form tries to generate a CSRF token.
        // We replace the manager with a stub that always returns a valid token.
        $csrfManager = $this->createMock(CsrfTokenManagerInterface::class);
        $csrfManager->method('getToken')->willReturnCallback(
            static fn (string $id) => new CsrfToken($id, 'test-token')
        );
        $csrfManager->method('refreshToken')->willReturnCallback(
            static fn (string $id) => new CsrfToken($id, 'test-token')
        );
        $csrfManager->method('isTokenValid')->willReturn(true);
        $csrfManager->method('removeToken')->willReturn('test-token');

        self::getContainer()->set('security.csrf.token_manager', $csrfManager);
    }

    private function stubApiClient(): void
    {
        $container = self::getContainer();

        $httpClient = new MockHttpClient(function (string $method, string $url, array $options = []): MockResponse {
            // MockHttpClient normalizes the 'json' option to a JSON-encoded 'body' string
            // before invoking this callback (via HttpClientTrait::prepareRequest).
            $rawBody = $options['body'] ?? null;
            $this->capturedApiRequests[] = [
                'method' => $method,
                'url' => $url,
                'body' => \is_string($rawBody) ? json_decode($rawBody, true) : null,
            ];

            // Sort so method-specific mocks take precedence over method-less ones.
            $mocks = $this->apiResponses;
            usort($mocks, static fn (array $a, array $b): int => (null !== $b['method']) <=> (null !== $a['method']));

            foreach ($mocks as $mock) {
                if (null !== $mock['method'] && $mock['method'] !== $method) {
                    continue;
                }
                if (str_contains($url, $mock['path'])) {
                    return new MockResponse(
                        json_encode($mock['data']),
                        [
                            'http_code' => $mock['status'],
                            'response_headers' => ['content-type' => 'application/ld+json'],
                        ]
                    );
                }
            }

            return new MockResponse(
                json_encode(['hydra:member' => [], 'hydra:totalItems' => 0]),
                ['response_headers' => ['content-type' => 'application/ld+json']]
            );
        });

        $container->set(Client::class, new Client(
            $container->get(Security::class),
            $container->get(EventDispatcherInterface::class),
            self::$kernel,
            $httpClient,
            ['base_uri' => 'https://haproxy:8080'],
        ));
    }

    private function stubUserProvider(): void
    {
        // The real UserProvider::refreshUser() decodes the user's JWT via JWTReader,
        // which fails on the fake token used by TestUser. The security ContextListener
        // calls refreshUser() on every request (including LiveComponent calls),
        // so we extend the provider and short-circuit refreshUser() to return the user as-is.
        // We must extend the concrete class (not just implement UserProviderInterface)
        // because FormAuthenticator type-hints the concrete UserProvider.
        $stub = new class(self::getContainer()->get(JWTReader::class)) extends UserProvider {
            public function refreshUser(UserInterface $user): UserInterface
            {
                return $user;
            }

            public function loadUserByIdentifier(string $identifier): UserInterface
            {
                throw new \LogicException('Not used in tests.');
            }
        };

        self::getContainer()->set(UserProvider::class, $stub);
    }
}
