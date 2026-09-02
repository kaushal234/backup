<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Contact;

use App\Controller\Contact\IndexController;
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
    private const BASE_URL = '/contact-us';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testContactPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testFormValidationFailWhenMessageIsBlank(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals(self::BASE_URL);

        // The department field is a card-based UI backed by a hidden native select.
        // Set its value via JS to avoid ElementNotInteractableException.
        $client->executeScript("document.querySelector('select[name=\"contact[department]\"]').value = 'Service';");

        $form = $crawler->selectButton('Send')->form([
            'contact[message]' => ' ',
        ], Request::METHOD_POST);

        $client->submit($form);

        self::assertPathEquals(self::BASE_URL);
        self::assertSelectorTextContains('div.alert', 'Message: This value should not be blank.');
    }

    public function testEmailIsSent(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals(self::BASE_URL);

        $client->executeScript("document.querySelector('select[name=\"contact[department]\"]').value = 'Service';");

        $form = $crawler->selectButton('Send')->form([
            'contact[message]' => 'Please give me information',
        ], Request::METHOD_POST);

        $client->submit($form);

        self::assertPathEquals(self::BASE_URL);
        self::assertSelectorTextContains('div.alert', 'Your email has been sent successfully.');
    }
}
