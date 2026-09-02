<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\EquipmentRecord;

use App\Controller\EquipmentRecord\ShowController;
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
    private const BASE_URL = '/equipments/1';
    private const BASE_URL_WITH_SERIAL = '/equipments/21';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testEquipmentPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testEquipmentDetailsAreDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);
        $serviceBulletinTitle = 'NBL BRAKE PEDAL ADJUSTMENT';
        $this->assertStringContainsString(
            $serviceBulletinTitle,
            $client->getPageSource()
        );

        self::assertPathEquals(self::BASE_URL);
    }

    public function testCustomerFilesSectionIsDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);

        $this->assertStringContainsString(
            'Customer Files',
            $client->getPageSource()
        );

        self::assertPathEquals(self::BASE_URL);
    }

    public function testEquipmentSerialDetailsAreDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL_WITH_SERIAL);
        $serialComponentName = 'SCHEM, ELEC';
        $this->assertStringContainsString(
            $serialComponentName,
            $client->getPageSource()
        );

        self::assertPathEquals(self::BASE_URL_WITH_SERIAL);
    }

    public function testEquipmentPageCanBeAccessedBySerialNumber(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(
            Request::METHOD_GET,
            '/equipments/serial_number/T85401'
        );

        $client->waitFor('body');

        self::assertSelectorTextContains('body', 'T85401');

        self::assertPathEquals('/equipments/serial_number/T85401');
    }

    public function testEquipmentPageRedirectsWhenSerialNumberDoesNotExist(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(
            Request::METHOD_GET,
            '/equipments/serial_number/UNKNOWN_SERIAL'
        );

        $client->waitFor('.alert-danger');

        self::assertSelectorTextContains(
            '.alert-danger',
            'No equipment found for serial number: UNKNOWN_SERIAL'
        );
    }
}
