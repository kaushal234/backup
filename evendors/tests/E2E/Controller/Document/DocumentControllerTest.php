<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Document;

use App\Controller\Settings\SettingsController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see SettingsController
 */
final class DocumentControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    private const BASE_URL = '/document';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testDocumentPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals('/security/login');
    }

    public function testDocumentPageIsDisplayedWhenAuthenticated(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertSelectorTextSame('h3', 'Documents');
        self::assertSelectorTextContains('div', 'Showing 1 to 5 of 5 entries');
    }
}
