<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\VendorWarrantyClaim;

use App\Controller\VendorWarrantyClaim\ShowNCRController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see ShowNCRController
 */
final class ShowNCRControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    private const BASE_URL = '/vendor-warranty-claim/ncr/show/1';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testShowNCRPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals('/security/login');
    }
}
