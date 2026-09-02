<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Forecast;

use App\Controller\Forecast\DetailedController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see DetailedController
 */
final class DetailedControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    private const BASE_URL = '/forecast/detailed';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testDetailedForecastPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals('/security/login');
    }

    public function testDetailedForecastPageIsDisplayedWhenAuthenticated(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertSelectorTextSame('h3', 'Forecast');
    }
}
