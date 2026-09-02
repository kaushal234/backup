<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\TechnicianOnCall;

use App\Controller\TechnicianOnCall\IndexController;
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
    private const string BASE_URL = '/technician-on-calls';

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

    public function testDisplayOfTechnicianOnCallList(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);
        $numberOfItems = $crawler->filter('#kreyu_data_table_technician_on_call tbody tr')->count();

        self::assertPathEquals(self::BASE_URL);

        self::assertSelectorTextContains('h2.section-title', 'Technician On Call');
        self::assertGreaterThan(10, $numberOfItems);
        self::assertSelectorTextContains('#kreyu_data_table_technician_on_call', 'CAMPAIGN User');
        self::assertSelectorTextContains('#kreyu_data_table_technician_on_call', 'LEPERS Julien');
        self::assertSelectorTextContains('#kreyu_data_table_technician_on_call', 'TXL-737');
        self::assertSelectorTextContains('#kreyu_data_table_technician_on_call', 'SN_001');
        self::assertSelectorExists('#filter_technician_on_call_status_value option[value="PENDING"][selected="selected"]');
        self::assertSelectorExists('#filter_technician_on_call_status_value option[value="IN_PROGRESS"][selected="selected"]');
        self::assertSelectorExists('#filter_technician_on_call_status_value option[value="SUSPENDED"][selected="selected"]');
        self::assertSelectorNotExists('#filter_technician_on_call_status_value option[value="SOLVED"][selected="selected"]');
        self::assertSelectorNotExists('#filter_technician_on_call_status_value option[value="CLOSED"][selected="selected"]');

        // "julien.lepers@tld.com" holds the role_TOC ACL on his active customer: the create button is shown.
        self::assertSelectorExists('a[href="/technician-on-calls/create"]');
    }

    public function testCreateButtonHiddenForUserWithoutTocRole(): void
    {
        $client = self::createPantherClient();
        // "user-campaign@tld.com" only has the role_ST ACL: the create button must be hidden.
        self::loginUser('user-campaign@tld.com', 'password');

        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals(self::BASE_URL);
        self::assertSelectorTextContains('h2.section-title', 'Technician On Call');
        self::assertSelectorNotExists('a[href="/technician-on-calls/create"]');
    }
}
