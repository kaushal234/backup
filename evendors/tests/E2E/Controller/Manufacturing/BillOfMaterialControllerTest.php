<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Manufacturing;

use App\Controller\Manufacturing\BillOfMaterialController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see BillOfMaterialController
 */
final class BillOfMaterialControllerTest extends PantherTestCase
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
        $client->request(Request::METHOD_GET, '/manufacturing/bill-of-material/500/6533033/2018-01-21T23:00:00Z');
        self::assertPathEquals('/security/login');
    }

    public function testBillOfMaterialsPageIsForbiddenWhenNotAllowed(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, '/');

        self::loginUser();

        $client->request(Request::METHOD_GET, '/manufacturing/bill-of-material/500/1186245/2018-01-21T23:00:00Z');
        self::assertSelectorTextContains('div.alert.alert-dismissible.alert-danger', 'Something went wrong');
        self::assertPathEquals('/');
    }

    public function testBillOfMaterialsPageIsDisplayedWhenAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, '/');

        self::loginUser();

        $client->request(Request::METHOD_GET, '/manufacturing/bill-of-material/500/6533033/2018-01-21T23:00:00Z');
        self::assertSelectorTextContains('h3', 'BOM for 6533033 from company 500 as');
    }
}
