<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\ManualDocument;

use App\Controller\ManualDocument\ShowController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see ShowController
 */
final class ShowControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/manual-documents/4';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testManualDocumentPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testManualDocumentPageIsNotAccessibleWhenNotGranted(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, '/');

        self::loginUser();

        $client->request(Request::METHOD_GET, '/manual-documents/5');
        self::assertSelectorTextContains('div.alert.alert-dismissible.alert-danger', 'Something went wrong');
        self::assertPathEquals('/');
    }

    public function testManualDocumentDetailsAreDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);
        $manualDocumentDescription = 'INSTRUMENT PANEL';
        $this->assertStringContainsString(
            $manualDocumentDescription,
            $client->getPageSource()
        );

        self::assertPathEquals(self::BASE_URL);
    }
}
