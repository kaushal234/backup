<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\SupplierCorrectiveActionRequest;

use App\Controller\SupplierCorrectiveActionRequest\ShowController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function Symfony\Component\String\u;

/**
 * @group functional
 *
 * @see ShowController
 */
final class ShowControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    private const BASE_URL = '/supplier-corrective-action-request/1';

    /**
     * @return iterable<array{0: string}>
     */
    public function provideBaseUris(): iterable
    {
        yield [self::BASE_URL];
    }

    /**
     * @dataProvider provideBaseUris
     */
    public function testShowControllerInvokeUnauthorized(string $uri): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, $uri);
        self::assertResponseRedirectsToLogin();
    }

    public function testShowControllerInvokeFailureNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $client = self::createClient();
        $this::loginUser($client);
        $client->request(Request::METHOD_GET, u(self::BASE_URL)->replace('/1', '/404')->toString());
    }

    /**
     * @dataProvider provideBaseUris
     */
    public function testShowControllerInvokeSuccess(string $uri): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request(Request::METHOD_GET, $uri);
        self::assertResponseIsSuccessful();
    }
}
