<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\TechnicianOnCall;

use App\Controller\TechnicianOnCall\ShowController;
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
    private const BASE_URL = '/technician-on-calls/44';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testTechnicianOnCallPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testTechnicianOnCallDetailsAreDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals(self::BASE_URL);
        self::assertSelectorTextContains('body', 'Technician On Call');
        self::assertSelectorTextContains('body', 'SERVICE user');
        self::assertSelectorTextContains('body', 'IN_PROGRESS');
        self::assertSelectorTextContains('body', 'AST BU2 user');
        self::assertSelectorTextContains('body', 'SN_003');
        self::assertSelectorTextContains('body', 'Belt Loaders');
        self::assertSelectorTextContains('body', 'TXL-737');
        self::assertSelectorTextContains('body', 'location_sso');
        self::assertSelectorTextContains('body', 'customer_for_fur');
        self::assertSelectorTextContains('body', 'CAMPAIGN User');
        self::assertSelectorTextContains('body', 'CDG');
        self::assertSelectorTextContains('body', 'TOC For Extranet');
        self::assertSelectorTextContains('body', 'This TOC has comments for extranet');

        $images = $crawler->filter('.dt-wrap .swiper-slide');
        self::assertGreaterThan(0, $images->count(), 'No image found');

        self::assertSelectorTextContains('#kreyu_data_table_comment', 'AST user');
        self::assertSelectorTextContains('#kreyu_data_table_comment', 'Public comment TOC#44');
        self::assertSelectorTextNotContains('#kreyu_data_table_comment', 'Private comment TOC#44');
    }
}
