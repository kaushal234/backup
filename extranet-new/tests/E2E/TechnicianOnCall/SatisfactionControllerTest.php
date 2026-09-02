<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\TechnicianOnCall;

use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see SatisfactionControllerTest
 */
final class SatisfactionControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/technician-on-calls/45/satisfaction/token';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testTechnicianOnCallSurveyDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals(self::BASE_URL);
        self::assertSelectorTextContains('body', 'Satisfaction Survey');
        self::assertSelectorTextContains('body', 'TOC#45');
    }
}
