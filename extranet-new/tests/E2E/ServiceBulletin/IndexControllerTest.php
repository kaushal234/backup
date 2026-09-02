<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\ServiceBulletin;

use App\Controller\ServiceBulletin\IndexController;
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
    private const BASE_URL = '/services/service-bulletins';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testServiceBulletinsAreDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals(self::BASE_URL);

        $rows = $crawler->filter('table tbody tr');
        self::assertGreaterThan(0, $rows->count(), 'No service bulletin row found');

        $this->assertStringContainsString(
            'NBL BRAKE PEDAL ADJUSTMENT',
            $client->getPageSource()
        );
    }
}
