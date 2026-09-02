<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Manual;

use App\Controller\Manual\ShowController;
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
    private const BASE_URL = '/manuals/2';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testManualPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testManualPageIsNotAccessibleWhenNotGranted(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, '/');

        self::loginUser();

        $client->request(Request::METHOD_GET, '/manuals/4');
        self::assertSelectorTextContains('div.alert.alert-dismissible.alert-danger', 'Something went wrong');
        self::assertPathEquals('/');
    }

    public function testManualDetailsAreDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);
        $manualDescription = 'precise description of this manual';
        $this->assertStringContainsString(
            $manualDescription,
            $client->getPageSource()
        );

        self::assertPathEquals(self::BASE_URL);
    }
}
