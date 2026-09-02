<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\KPI;

use App\Controller\KPI\IndexController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see IndexController
 */
final class IndexControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    private const BASE_URL = '/kpi';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testKPIPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals('/security/login');
    }

    public function testKPIPageIsDisplayedWhenAuthenticated(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertSelectorTextSame('h5', 'Select a supplier to generate reports');
    }

    public function testSubmittingSupplierDisplayReports(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);
        $form = $crawler->selectButton('Submit')->form([
            'report_filter[supplierNumber]' => 'DAN0013',
            'report_filter[factory]' => 'ALL',
        ], Request::METHOD_POST);
        $client->submit($form);
        self::assertSelectorTextContains('body', 'Top part failure');
        self::assertSelectorTextContains('body', 'Part failure history');
        self::assertSelectorTextContains('body', 'VWC Supplier history (36 months)');
        self::assertSelectorTextContains('body', 'SCAR Supplier history (36 months)');
        self::assertSelectorTextContains('body', 'Supplier recovery cost');
        self::assertSelectorTextContains('body', 'VWC in VENDOR_TO_RESPOND status');
    }
}
