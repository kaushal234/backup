<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Security;

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
    private const BASE_URL = '/';

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
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testInformationAreDisplayedOnHomePage(): void
    {
        $crawler = self::loginUser();
        self::assertPathEquals(self::BASE_URL);

        $tiles = $crawler->filter('.quick-tile')->getIterator();

        self::assertCount(4, $tiles);
    }
}
