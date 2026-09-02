<?php

declare(strict_types=1);

namespace App\Tests\E2E\Twig\LiveComponents;

use App\Test\Helpers\AutocompleteTrait;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 */
class TechnicianOnCallLiveComponent extends PantherTestCase
{
    use AutocompleteTrait;
    use PantherBrowserTrait;

    private const BASE_URL = '/technician-on-calls/create';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testTechnicianOnCallCreationPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testFormRendersEmptyWithoutEquipmentId(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals(self::BASE_URL);

        $client->waitFor('[data-controller="live"]');

        self::assertSelectorExists('form');
        self::assertTomSelectValue($client, 'technician_on_call_equipment', '');
    }

    public function testFormPrefilledWhenEquipmentIdProvided(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $equipmentId = '1';
        $client->request('GET', \sprintf('%s?equipmentRecordId=%s', self::BASE_URL, $equipmentId));
        self::assertPathEquals(self::BASE_URL);

        $client->waitFor('[data-controller="live"]');

        self::assertTomSelectValue($client, 'technician_on_call_equipment', '1');
        self::assertTomSelectValue($client, 'technician_on_call_airport', '62');
        self::assertSelectorAttributeContains(
            'input#technician_on_call_hourMeter',
            'value',
            '10'
        );
    }

    public function testOnEquipmentChangeFillsRelatedFields(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals(self::BASE_URL);
        $client->waitFor('[data-controller="live"]');

        $client->getCrawler()
            ->filter('#technician_on_call_equipment-ts-control')
            ->click();

        $client->getKeyboard()->sendKeys('SN_001');

        $client->waitFor('#technician_on_call_equipment-ts-dropdown [data-value]');

        $client->getCrawler()
            ->filter('#technician_on_call_equipment-ts-dropdown [data-value]')
            ->first()
            ->click();

        $hourMeter = self::waitForInputValue($client, 'input#technician_on_call_hourMeter');
        self::assertSame('10', $hourMeter);

        self::assertTomSelectValue($client, 'technician_on_call_airport', '62');
    }

    public function testSaveWithValidDataRedirectsToIndex(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $equipmentId = '12';
        $client->request('GET', \sprintf('%s?equipmentRecordId=%s', self::BASE_URL, $equipmentId));
        self::assertPathEquals(self::BASE_URL);

        $client->waitFor('[data-controller="live"]');
        $client->waitFor('#technician_on_call_equipment ~ .ts-wrapper.has-items');
        $client->waitFor('#technician_on_call_airport ~ .ts-wrapper.has-items');

        $client->getCrawler()
            ->filter('input[name="technician_on_call[title]"]')
            ->sendKeys('title')
        ;

        $client->getCrawler()
            ->filter('textarea[name="technician_on_call[description]"]')
            ->sendKeys('description')
        ;

        $client->getCrawler()
            ->filter('input[name="technician_on_call[errorCodes]"]')
            ->sendKeys('ERR-001')
        ;

        $client->getCrawler()
            ->filter('input[name="technician_on_call[hourMeter]"]')
            ->sendKeys('10')
        ;

        $client->getCrawler()
            ->filter('select[name="technician_on_call[serviceActivity]"]')
            ->children('option[value="/service/service_activities/1"]')
            ->click()
        ;

        $client->getCrawler()
            ->filter('select[name="technician_on_call[unitOperationalStatus]"]')
            ->children('option[value="/unit_operational_statuses/MCF"]')
            ->click()
        ;

        $client->getCrawler()
            ->filter('button[data-action="live#action"][data-live-action-param="save"]')
            ->click()
        ;

        $client->waitFor('.alert-success');
        self::assertPathEquals('/technician-on-calls');
    }
}
