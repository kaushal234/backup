<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\RequestForQuotation;

use App\Controller\RequestForQuotation\ShowController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    private const BASE_URL = '/request-for-quotation/show/1/1';
    private const BASE_URL_NO_ERP = '/request-for-quotation/show/1';

    /**
     * @return iterable<array{0: string}>
     */
    public function provideBaseUris(): iterable
    {
        yield [self::BASE_URL];
        yield [self::BASE_URL_NO_ERP];
    }

    /**
     * @dataProvider provideBaseUris
     */
    public function testShowControllerInvokeUnauthorized(string $uri): void
    {
        $client = self::createClient();
        $client->request('GET', $uri);
        self::assertResponseRedirectsToLogin();
    }

    public function testShowControllerInvokeFailureNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', '/request-for-quotation/show/404');
    }

    /**
     * @dataProvider provideBaseUris
     */
    public function testShowControllerInvokeSuccess(string $uri): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', $uri);
        self::assertResponseIsSuccessful();
    }
}
