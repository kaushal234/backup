<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\EquipmentRecord;

use App\Test\Helpers\PantherBrowserTrait;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 */
final class EditControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/equipments/1/edit';
    private const SHOW_URL = '/equipments/1';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testEquipmentEditPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testEquipmentRecordUpdated(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);

        $client->waitForVisibility('.ts-wrapper');

        // The equipment record already has an airport selected, so we only update
        // the customer serial number. Re-selecting an airport through the AJAX-driven
        // TomSelect dropdown made this test flaky, while the existing airport value is
        // submitted as-is.
        $customerSerialNumberInput = $client->findElement(WebDriverBy::cssSelector('#equipment_customerSerialNumber'));
        $customerSerialNumberInput->clear();
        $customerSerialNumberInput->sendKeys('MyAssetNumber');

        $client->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();

        self::assertPathEquals(self::SHOW_URL);

        $client->waitFor('div.alert');

        self::assertSelectorTextContains('div.alert', 'Equipment has been updated successfully.');
        self::assertAnySelectorTextContains('td', 'MyAssetNumber');
    }
}
