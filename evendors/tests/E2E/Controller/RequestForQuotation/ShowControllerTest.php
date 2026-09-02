<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\RequestForQuotation;

use App\Controller\PurchaseOrder\ShowController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see ShowController
 */
final class ShowControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    private const BASE_URL = '/request-for-quotation/';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testShowRFQPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, '/request-for-quotation/show/1');
        self::assertPathEquals('/security/login');
    }

    public function testShowRFQPageIsDisplayedWhenAuthenticated(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $client->request('GET', '/request-for-quotation/show/RFQ000005/500');
        self::assertSelectorTextContains('h3', '#RFQ000005');
    }
}
