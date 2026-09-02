<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\VendorWarrantyClaim;

use App\Controller\VendorWarrantyClaim\IndexController;
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

    private const BASE_URL = '/vendor-warranty-claim/';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testVWCPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals('/security/login');
    }

    public function testVWCPageIsDisplayedWhenAuthenticated(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertSelectorTextContains('h3', 'Vendor Warranty Claim');
    }
}
