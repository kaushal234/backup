<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\PurchaseOrder;

use App\Controller\PurchaseOrder\ShowController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

use function sprintf;

/**
 * @group e2e
 *
 * @see ShowController
 */
final class ShowControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    private const BASE_URL = '/purchase-order';

    private array $linesTableHeadersList = [
        'Line',
        'Part Number',
        'Supplier Item code',
        'Line Status',
        'Description',
        'Rev',
        'Status',
        'Ordered Quantity',
        'Delivered',
        'Back Order',
        'Original Requested Date',
        'Last Rescheduled Date',
        'Confirmed Delivery Date',
        'Price',
        'Line Delivery status',
        'Line Comment',
    ];

    private array $recurrentInformation = ['lineIdentifier', 'partNumber', 'description', 'revision', 'expired', 'supplierItemCode'];

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testShowPurchaseOrderPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals('/security/login');
    }

    public function testShowPurchaseOrderPageIsDisplayedWhenAuthenticated(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $client->request(Request::METHOD_GET, '/purchase-order/show/V50483368/500');
        self::assertSelectorTextContains('h3', '#V50483368');
    }

    public function testLinesTableHeadersList(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, '/purchase-order/show/V50483368/500');
        $client->waitFor('#purchase-order-lines-table thead');
        $headers = $crawler->filter('#purchase-order-lines-table thead tr')->text();
        foreach ($this->linesTableHeadersList as $header) {
            $this->assertMatchesRegularExpression(sprintf('/%s/', $header), $headers);
        }
    }

    /**
     * View only line where we can confirm the delivery date.
     */
    public function testOpenLineView(): void
    {
        $client = self::createPantherClient();
        self::loginUser();
        $client->request(Request::METHOD_GET, '/purchase-order/show/V50483773/500');

        $client->waitFor('input#flexRadioOpenLine', 5000);
        $client->waitFor('input#flexRadioAll', 5000);

        $html = $client->getInternalResponse()->getContent();

        $crawler = new Crawler($html);
        $flexRadioOpenHtml = $crawler->filter('input#flexRadioOpenLine')->outerHtml();
        $this->assertMatchesRegularExpression(sprintf('/%s/', 'checked'), $flexRadioOpenHtml);
        $flexRadioOpenHtml = $crawler->filter('input#flexRadioAll')->outerHtml();

        $this->assertDoesNotMatchRegularExpression(sprintf('/%s/', 'checked'), $flexRadioOpenHtml);
    }

    /**
     * When line have remainders ( split in multiple lines ) hide recurrent information.
     */
    public function testHideRecurrentInformationWhenViewAllLine(): void
    {
        $client = self::createPantherClient();
        self::loginUser();
        $crawler = $client->request(Request::METHOD_GET, '/purchase-order/show/V50483773/500');

        $client->waitFor('input#flexRadioAll', 5000);

        $inputCrawler = $crawler->filter('input#flexRadioAll');
        $inputElement = $inputCrawler->first();
        $inputElement->click();

        foreach ($this->recurrentInformation as $information) {
            $this->assertMatchesRegularExpression(sprintf('/%s/', 'data-name="'.$information.'" style="display: none;"'), $crawler->html());
        }
    }

    /**
     * notOpenLine : Line where we can't confirm the delivery date
     * Line already delivered
     * Line canceled
     * attribute data-is-confirmable="0".
     */
    public function testHideNotOpenLine(): void
    {
        $client = self::createPantherClient();
        self::loginUser();
        $client->request(Request::METHOD_GET, '/purchase-order/show/V50483773/500');

        $client->waitFor('table#purchase-order-lines-table tbody tr[style="display: none;"]', 5000);

        $hiddenLines = $client->executeScript('
            return Array.from(document.querySelectorAll("table#purchase-order-lines-table tbody tr")).filter(tr => tr.style.display === "none").map(tr => tr.outerHTML);
        ');

        $this->assertNotEmpty($hiddenLines, 'No hidden lines found');
        $this->assertStringContainsString('data-is-confirmable="0"', $hiddenLines[0], 'Hidden line does not contain the expected attribute');
    }

    /**
     * from view open line to view All line.
     */
    public function testSwitchViewAllLine(): void
    {
        $client = self::createPantherClient();
        self::loginUser();
        $crawler = $client->request(Request::METHOD_GET, '/purchase-order/show/V50483773/500');

        $client->waitFor('table#purchase-order-lines-table tbody tr[style="display: none;"]', 5000);

        $countHideLinesBefore = $crawler->filter('table#purchase-order-lines-table tbody tr[style="display: none;"]')->count();
        $this::assertGreaterThan(0, $countHideLinesBefore);

        $inputCrawler = $crawler->filter('input#flexRadioAll');
        $inputElement = $inputCrawler->first();
        $inputElement->click();

        $countHideLinesAfter = $crawler->filter('table#purchase-order-lines-table tbody tr[style="display: none;"]')->count();
        $this::assertGreaterThan($countHideLinesAfter, $countHideLinesBefore);
        $this::assertSame(0, $countHideLinesAfter);
    }
}
