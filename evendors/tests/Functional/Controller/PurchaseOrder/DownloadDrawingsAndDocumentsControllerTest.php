<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\PurchaseOrder;

use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group functional
 *
 * @see DownloadDrawingsAndDocumentsController
 */
final class DownloadDrawingsAndDocumentsControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    private const BASE_URL = '/purchase-order/download_drawings_and_documents/1/1';

    public function testDownloadControllerInvokeSuccess(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', self::BASE_URL);
        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertInstanceOf(BinaryFileResponse::class, $client->getResponse());
    }

    public function testDownloadControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', self::BASE_URL);
        self::assertResponseRedirectsToLogin();
    }

    public function testDownloadControllerInvokeNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', '/purchase-order/download_drawings_and_documents/404/404');
    }
}
