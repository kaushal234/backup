<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Manufacturing;

use App\Controller\Manufacturing\BillOfMaterialDrawingController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see BillOfMaterialDrawingController
 */
final class BillOfMaterialDrawingControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testBillOfMaterialsPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, '/manufacturing/bill-of-material-drawing/500/6533033/2018-01-21T23:00:00Z');
        self::assertPathEquals('/security/login');
    }

    public function testBillOfMaterialsDrawingIsDownloadWhenAuthenticated(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, '/manufacturing/bill-of-material-drawing/500/6533033-101/2018-01-21T23:00:00Z');

        $pageSource = $client->getPageSource();
        $this->assertStringContainsString('6533033-101.pdf', $pageSource);
    }

    public function testBillOfMaterialsDrawingIsUnauthorizedWhenAPIRejectsVendorUser(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, '/manufacturing/bill-of-material-drawing/500/1186245/2018-01-21T23:00:00Z');
        self::assertSelectorTextContains('div.alert.alert-dismissible.alert-danger', 'Something went wrong');
        self::assertPathEquals('/');
    }

    public function testMessageBillOfMaterialsDrawingWhenFileNotExist(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, '/manufacturing/bill-of-material-drawing/500/6533033-104/2018-01-21T23:00:00Z');

        $pageSource = $client->getPageSource();
        $this->assertStringNotContainsString('type="application/pdf"', $pageSource);

        self::assertSelectorTextContains('div', 'The file linked to this item is empty.');
    }
}
