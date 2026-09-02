<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\TermsOfUse;

use App\Controller\TermsOfUse\IndexController;
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

    private const BASE_URL = '/terms-of-use/';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testTermsOfUsePageIsDisplayed(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertSelectorTextContains('h1', 'TERMS AND CONDITIONS OF USE');
    }
}
