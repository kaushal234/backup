<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\ServiceBulletin;

use App\Controller\ServiceBulletin\ShowController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see ShowController
 */
class ShowControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/service_bulletin/59';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testEquipmentPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testEquipmentDetailsAreDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);

        $serviceBulletinTitle = 'NBL BRAKE PEDAL ADJUSTMENT';
        $this->assertStringContainsString(
            $serviceBulletinTitle,
            $client->getPageSource()
        );

        $images = $crawler->filter('.dt-wrap .swiper-slide');
        self::assertGreaterThan(0, $images->count(), 'No image found');

        self::assertPathEquals(self::BASE_URL);
    }
}
