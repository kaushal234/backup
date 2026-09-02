<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Catalogue;

use App\Controller\Catalogue\ShowProductFamiliesController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see ShowProductFamiliesController
 */
final class ShowProductFamiliesControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/catalogue/1/product-families';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testProductFamiliesPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testProductFamiliesAreDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);

        $cards = $crawler->filter('.cat-card')->getIterator();

        self::assertAnySelectorTextContains('a', 'Famille To Test Same Name Is Ok');
        self::assertPathEquals(self::BASE_URL);
        self::assertCount(9, $cards);
    }
}
