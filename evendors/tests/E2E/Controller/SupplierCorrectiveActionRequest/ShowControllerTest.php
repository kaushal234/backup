<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\SupplierCorrectiveActionRequest;

use App\Controller\SupplierCorrectiveActionRequest\ShowController;
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

    private const BASE_URL = '/supplier-corrective-action-request/3';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testShowSCARPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals('/security/login');
    }

    public function testShowSCARPageIsDisplayedWhenAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertSelectorTextContains('h3', 'SCAR #3');
    }
}
