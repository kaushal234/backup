<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Catalogue;

use App\Controller\Catalogue\ShowProductFamilyController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see ShowProductFamilyController
 */
final class ShowProductFamilyControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/catalogue/product-families/15';

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

        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertAnySelectorTextContains('a', 'TYPE ENGLISH');
        self::assertAnySelectorTextContains('a', 'CATALOGUE');
        self::assertAnySelectorTextContains('.ds-title', 'Datasheets');

        self::assertPathEquals(self::BASE_URL);
    }
}
