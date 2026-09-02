<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\EquipmentRecord;

use App\Controller\EquipmentRecord\SerialsController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see SerialsController
 */
final class SerialsControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    // Equipment record "BRIAN" (id 14), accessible to julien.lepers@tld.com as buyer.
    private const BASE_URL = '/equipments/14/serials';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testSerialsPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testSerialsAreDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals(self::BASE_URL);
        $client->waitFor('table');

        // The page lists the non-schematics serials (major components) of the equipment.
        self::assertSelectorTextContains('body', 'BRIAN');
        self::assertSelectorTextContains('table', 'OBU, LINK');
    }
}
