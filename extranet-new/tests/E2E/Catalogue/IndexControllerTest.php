<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Catalogue;

use App\Controller\Catalogue\IndexController;
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
    private const BASE_URL = '/catalogue';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testCataloguePageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testProductTypesAreDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();
        $client->waitFor('.brand-mark'); // Ensure homepage settled before navigating

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);

        $cards = $crawler->filter('.cat-card')->getIterator();

        self::assertPathEquals(self::BASE_URL);
        self::assertCount(1, $cards);
    }
}
