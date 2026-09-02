<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\TechnicianOnCall;

use App\Controller\TechnicianOnCall\CreateController;
use App\Test\Helpers\PantherBrowserTrait;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e2
 *
 * @see CreateController
 */
class CreateControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/technician-on-calls/create';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testTechnicianOnCallCreatePageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    /**
     * @group e2e
     */
    public function testCreatePageDeniedForUserWithoutTocRole(): void
    {
        $client = self::createPantherClient();
        // "user-campaign@tld.com" only has the role_ST ACL: access to the create page must be denied.
        self::loginUser('user-campaign@tld.com', 'password');

        // Visit an accessible page first so it becomes the referer.
        $client->request(Request::METHOD_GET, '/technician-on-calls');
        self::assertPathEquals('/technician-on-calls');

        // Navigate via window.location so the browser sends the Referer header
        // (a direct $client->request() does not set it).
        $client->executeScript(\sprintf('window.location.href = %s;', json_encode(self::BASE_URL)));

        // Authenticated but unauthorized: the AccessDeniedHandler redirects back to the referer
        // (with a flash message) instead of rendering the create form.
        $client->wait(5)->until(
            static fn () => !str_contains((string) $client->getCurrentURL(), '/create')
        );
        self::assertPathEquals('/technician-on-calls');
        self::assertSelectorNotExists('[data-controller="live"]');
    }

    public function testCreateTechnicianOnCallWithAutocomplete(): void
    {
        $client = static::createPantherClient();
        self::loginUser();

        $crawler = $client->request('GET', self::BASE_URL);

        $client->findElement(WebDriverBy::id('technician_on_call_equipment_serialNumber'))
            ->sendKeys('PE');
        $client->waitFor('.autocomplete-list li');
        $client->findElements(WebDriverBy::cssSelector('.autocomplete-list li'))[0]->click();

        $client->findElement(WebDriverBy::id('technician_on_call_airport_airport'))
            ->sendKeys('CD');
        $client->waitFor('.autocomplete-list li');
        $client->findElements(WebDriverBy::cssSelector('.autocomplete-list li'))[0]->click();

        $client->submitForm('Create', [
            'technician_on_call[equipmentRecord]' => '/equipment_records/1',
            'technician_on_call[airport]' => '/airports/1',
            'technician_on_call[title]' => 'Created for tests',
            'technician_on_call[description]' => 'Created from test',
            'technician_on_call[errorCodes]' => 'ERR-42',
            'technician_on_call[hourMeter]' => 1234,
            'technician_on_call[serviceActivity]' => '/service/service_activities/1',
            'technician_on_call[unitOperationalStatus]' => '/unit_operational_statuses/MCF',
        ]);

        $client->wait(5)->until(
            static fn () => self::BASE_URL !== $client->getCurrentURL()
        );

        self::assertPathEquals('/technician-on-calls');
    }
}
