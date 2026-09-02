<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\PurchaseOrder;

use App\Controller\PurchaseOrder\IndexController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see IndexController
 */
final class EditAllPurchaseOrderControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    private const BASE_URL = '/purchase-order/edit_all_delivery_date';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testEditAllPurchaseOrderPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals('/security/login');
    }

    public function testEditAllPurchaseOrderPageIsDisplayedWhenAuthenticated(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);
        $countLines = $crawler->filter('#purchase-orders-lines-table tbody tr')->count();
        $this::assertSame(51, $countLines);
        self::assertSelectorTextContains('h3', 'Orders in progress');
    }

    public function testEditAllPurchaseOrderPageStateFilterWork(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL.'?state=Unconfirmed');
        $countLines = $crawler->filter('#purchase-orders-lines-table tbody tr')->count();

        $this::assertSame(1, $countLines);
    }
}
