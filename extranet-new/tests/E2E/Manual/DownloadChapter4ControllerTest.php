<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Manual;

use App\Controller\Manual\DownloadChapter4Controller;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see DownloadChapter4Controller
 */
final class DownloadChapter4ControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/manuals/2/chapter-4';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testChapter4DownloadRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testChapter4DownloadIsNotAccessibleWhenNotGranted(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, '/');

        self::loginUser();

        $client->request(Request::METHOD_GET, '/manuals/4/chapter-4');
        self::assertSelectorTextContains('div.alert.alert-dismissible.alert-danger', 'Something went wrong');
        self::assertPathEquals('/');
    }

    public function testChapter4DownloadButtonIsDisplayedOnManualPage(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, '/manuals/2');

        self::assertSelectorExists('a[href="/manuals/2/chapter-4"]');
    }
}
