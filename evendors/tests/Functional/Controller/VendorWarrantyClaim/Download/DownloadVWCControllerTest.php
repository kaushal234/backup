<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\VendorWarrantyClaim\Download;

use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @group functional
 *
 * @see DownloadVWCControllerTest
 */
final class DownloadVWCControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    public function testXlsx(): void
    {
        $client = self::createClient();
        $this::loginUser($client);

        $client->request('GET', '/vendor-warranty-claim/download/all/xlsx');

        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertResponseHeaderSame('content-disposition', 'inline; filename=vendor-warranty-claims.xlsx');
        self::assertTrue($client->getResponse() instanceof BinaryFileResponse);
    }
}
