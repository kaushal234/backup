<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\WarrantyClaim;

use App\Controller\WarrantyClaim\IndexController;
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
    private const BASE_URL = '/warranty_claims';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testWarrantyClaimsPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testWarrantyClaimsAreDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);
        $client->waitFor('.card');

        self::assertSelectorExists('turbo-frame');
        self::assertSelectorExists('table');

        self::assertSame(
            self::BASE_URL,
            parse_url($client->getCurrentURL(), \PHP_URL_PATH)
        );
    }
}
