<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\VendorWarrantyClaim;

use App\Controller\VendorWarrantyClaim\ShowWCController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see ShowWCController
 */
final class ShowWCControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    private const BASE_URL = '/vendor-warranty-claim/wc/show/2';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testShowWCPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals('/security/login');
    }

    public function testShowWCPageIsDisplayedWhenAuthenticated(): void
    {
        $client = self::createPantherClient();

        self::loginUser();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertSelectorTextContains('h3', 'VWC #2');
        self::assertAnySelectorTextContains('td', '9436053'); // partNumber
    }
}
