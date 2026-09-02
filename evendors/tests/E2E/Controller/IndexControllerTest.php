<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller;

use App\Controller\IndexController;
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

    private const BASE_URL = '/document';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testHomepageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Evendors | TLD Group');
    }

    public function testElementsAreFullyLoadedOnTheHomePage(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, '/');

        self::loginUser();
        // test message is displayed after authentication
        self::assertSelectorTextContains('body', 'Philippe LAST');
        // test news
        self::assertSelectorExists('.fa-file');
        self::assertSelectorTextContains('p.card-text', 'My little pony 10/10 best drama ever');
        $client->waitFor('#ordersInProgressLink');
        // test orders statistics are loaded
        self::assertSelectorIsVisible('a#ordersInProgressLink');
        // test ranking graph is loaded
        self::assertSelectorIsVisible('div.highcharts');
    }
}
