<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\PurchaseOrder\Download;

use App\Controller\PurchaseOrder\Download\DownloadAllController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function sprintf;

/**
 * @group functional
 *
 * @see DownloadAllController
 */
final class DownloadAllControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use KernelBrowserTrait;

    private const BASE_URL = '/purchase-order/download/all/xlsx';

    public function testXlsx(): void
    {
        $client = self::createClient();
        $this::loginUser($client);

        $client->request('GET', self::BASE_URL);

        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertResponseHeaderSame('content-disposition', sprintf('%s; filename=purchase-orders.xlsx', 'inline'));
        self::assertInstanceOf(BinaryFileResponse::class, $client->getResponse());
    }

    public function testDownloadAllControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', self::BASE_URL);
        self::assertResponseRedirectsToLogin();
    }
}
