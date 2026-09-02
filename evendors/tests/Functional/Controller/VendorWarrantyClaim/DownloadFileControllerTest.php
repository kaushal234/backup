<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\VendorWarrantyClaim;

use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function Symfony\Component\String\u;

/**
 * @group functional
 *
 * @see DownloadFileController
 */
final class DownloadFileControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    private const BASE_URL_NCR = '/purchasing/ncr_vendor_warranty_claims/1/file/1';
    private const BASE_URL_WC = '/purchasing/wc_vendor_warranty_claims/1/file/1';

    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public function provideVendorWarrantyClaimsActions(): iterable
    {
        yield [self::BASE_URL_NCR];
        yield [self::BASE_URL_WC];
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsActions
     */
    public function testDownloadCommentFileControllerInvokeUnauthorized(string $url): void
    {
        $client = self::createClient();
        $client->request('GET', $url);
        self::assertResponseRedirectsToLogin();
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsActions
     */
    public function testDownloadCommentFileControllerInvokeNotFound(string $url): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', u($url)->replace('/1', '/404')->toString());
        self::assertResponseStatusNotFound();
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsActions
     */
    public function testDownloadCommentFileControllerInvokeSuccess(string $url): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', $url);
        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertTrue($client->getResponse() instanceof BinaryFileResponse);
    }
}
