<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\EquipmentRecord;

use App\Controller\EquipmentRecord\ShowPublicController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see ShowPublicController
 */
final class ShowPublicControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/public/GT2023';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testPublicEquipmentPageIsReachableWithoutAuthentication(): void
    {
        $client = self::createPantherClient();

        $client->request(Request::METHOD_GET, self::BASE_URL);

        // Public route: we should NOT be redirected to the login page.
        self::assertPathEquals(self::BASE_URL);
        $client->waitFor('body');
        self::assertSelectorTextContains('body', 'GT2023');
    }

    public function testPublicEquipmentPageIsReachableWhenAuthenticated(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);

        // Even when authenticated, we stay on the public URL.
        self::assertPathEquals(self::BASE_URL);
        $client->waitFor('body');
        self::assertSelectorTextContains('body', 'GT2023');
    }
}
