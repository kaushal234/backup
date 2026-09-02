<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Settings;

use App\Controller\Settings\SettingsController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see SettingsController
 */
final class SettingsControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    private const BASE_URL = '/settings';

    private const FORM_ACTION = '/settings';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testSettingsPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals('/security/login');
    }

    public function testSettingsPageIsDisplayedWhenAuthenticated(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertSelectorTextSame('h2', 'Settings');
    }

    public function testChangePasswordFormDisplaysErrorMessageWhenFormValidationFails(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);
        $form = $crawler->selectButton('Submit')->form([
            'change_password[password][first]' => 'darty1234',
            'change_password[password][second]' => 'darty1234',
        ], Request::METHOD_POST);
        $client->submit($form);
        self::assertSelectorTextContains('div.alert', 'Something went wrong during the update of your password.');
        self::assertSelectorTextContains('body', 'The new password must be 15 characters or more.');
    }

    public function testChangePasswordFormDisplaysErrorMessageWhenAPIValidationFails(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);
        $form = $crawler->selectButton('Submit')->form([
            'change_password[password][first]' => 'darty1234darty1234',
            'change_password[password][second]' => 'darty1234darty1234',
        ], Request::METHOD_POST);
        $client->submit($form);
        self::assertSelectorTextContains('div.alert', 'Something went wrong during the update of your password.');
        self::assertSelectorTextContains('body', 'Password must check 2 of these constraints: include both upper and lower case letters, include at least one number, include at least one special character');
    }

    public function testChangePasswordAndRelogWithNewPassword(): void
    {
        $client = self::createPantherClient();

        self::loginUser();

        // change password
        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);
        $form = $crawler->selectButton('Submit')
            ->form([
                'change_password[password][first]' => 'P@ssw0rd15charsP@ssw0rd15chars',
                'change_password[password][second]' => 'P@ssw0rd15charsP@ssw0rd15chars',
            ], Request::METHOD_POST)
        ;
        $client->submit($form);
        self::assertSelectorTextContains('div.alert', 'Your password has been updated successfully.');

        // logout
        $client->request(Request::METHOD_GET, '/security/logout');

        self::assertPathEquals('/security/login');

        // login with new password
        $crawler = $client->request(Request::METHOD_GET, '/security/login');
        $form = $crawler->selectButton('Continue')->form(['_identifier' => 'devteam@tld-america.com', '_password' => 'P@ssw0rd15charsP@ssw0rd15chars'], Request::METHOD_POST);
        $form['terms_of_use_agreement']->tick();
        $client->submit($form);

        // change password back to previous value
        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);
        $form = $crawler->selectButton('Submit')
            ->form([
                'change_password[password][first]' => 'P@ssw0rd15chars',
                'change_password[password][second]' => 'P@ssw0rd15chars',
            ], Request::METHOD_POST)
        ;
        $client->submit($form);
        self::assertSelectorTextContains('body', 'Your password has been updated successfully.');
    }
}
