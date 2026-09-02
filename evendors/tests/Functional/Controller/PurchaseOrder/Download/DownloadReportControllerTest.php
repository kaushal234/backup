<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\PurchaseOrder\Download;

use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function sprintf;

/**
 * @group functional
 *
 * @see DownloadReportController
 */
final class DownloadReportControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use KernelBrowserTrait;

    private const BASE_URL = '/purchase-order/download/report/2021-01-01%2000:00:00/2021-12-31%2023:59:59';

    public function testXlsxOk(): void
    {
        $client = self::createClient();
        $this::loginUser($client);

        $client->request('GET', self::BASE_URL);

        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertResponseHeaderSame('content-disposition', sprintf('%s; filename=purchase_orders_2021-01-01_2021-12-31.xlsx', 'inline'));
        self::assertInstanceOf(BinaryFileResponse::class, $client->getResponse());
    }

    public function testDownloadAllControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', self::BASE_URL);
        self::assertResponseRedirectsToLogin();
    }
}
