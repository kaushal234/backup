<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Contact;

use App\Controller\User\FormAccountController;
use App\Test\Helpers\PantherBrowserTrait;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see FormAccountController
 */
final class FormAccountControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/account/update';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testAccountFormPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testFormValidationFailedWhenDataIsIncorrect(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);

        $form = $crawler->selectButton('Update')->form([
            'user[lastname]' => ' ',
            'user[phone]' => 'NotAPhoneNumber',
        ], Request::METHOD_POST);

        $client->submit($form);

        self::assertPathEquals(self::BASE_URL);

        $client->waitFor('div.alert');

        self::assertAnySelectorTextContains('div.alert', 'Lastname: This value should not be blank.');
        self::assertAnySelectorTextContains('div.alert', 'Phone: This value is not a valid phone number, please use international format.');
    }

    public function testUserIdUpdated(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);

        $client->waitForVisibility('.ts-wrapper');

        $input = $client->findElement(WebDriverBy::cssSelector('.ts-control input'));
        $input->sendKeys('Estonia');

        $client->waitFor('.ts-dropdown .option');
        $client->findElement(WebDriverBy::cssSelector('.ts-dropdown .option'))->click();

        $form = $crawler->selectButton('Update')->form([
            'user[firstname]' => 'Peter',
            'user[lastname]' => 'McCalloway',
        ], Request::METHOD_POST);

        $client->submit($form);

        self::assertPathEquals(self::BASE_URL);

        $client->waitFor('div.alert');

        self::assertSelectorTextContains('div.alert', 'Your account has been updated successfully.');

        $client->request(Request::METHOD_GET, '/account');
        self::assertPathEquals('/account');

        self::assertAnySelectorTextContains('dd', 'Peter');
        self::assertAnySelectorTextContains('dd', 'MCCALLOWAY');
    }
}
