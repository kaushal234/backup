<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\SupplierCorrectiveActionRequest;

use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Symfony\Component\String\u;

/**
 * @group functional
 *
 * @see MainFileController
 */
final class MainFileControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    private const BASE_URL = '/supplier-corrective-action-request/1/main-file/1';

    public function testMainfileControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', self::BASE_URL);
        self::assertResponseRedirectsToLogin();
    }

    /**
     * Default file on 404. Not a 404 response.
     */
    public function testMainfileControllerInvokeNotFound(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', u(self::BASE_URL)->replace('/1', '/404')->toString());
        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertResponseHeaderSame('content-disposition', 'inline; filename=no_photo_catalogue.png');
        self::assertResponseHeaderSame('content-type', 'image/png');
        self::assertTrue($client->getResponse() instanceof StreamedResponse);
    }

    public function testMainfileControllerInvokeSuccess(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', self::BASE_URL);
        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertTrue($client->getResponse() instanceof StreamedResponse);
    }
}
