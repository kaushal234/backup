<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\RequestForQuotation;

use App\Controller\RequestForQuotation\IndexController;
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

    private const BASE_URL = '/request-for-quotation/';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testRFQPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals('/security/login');
    }

    public function testRFQPageIsDisplayedWhenAuthenticated(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertSelectorTextContains('h3', 'Request For Quotations');
    }
}
