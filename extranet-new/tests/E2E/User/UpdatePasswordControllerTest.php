<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Contact;

use App\Controller\User\UpdatePasswordController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see UpdatePasswordController
 */
final class UpdatePasswordControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/account/update-password';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testUpdatePasswordPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testEmailIsSentWhenRequestingThePageWithoutToken(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertAnySelectorTextContains('div.alert', 'An email has been sent to your email address with a link to update your password.');
    }

    public function testValidationFailWhenPasswordsMismatch(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, \sprintf('%s?updateToken=this-is-not-a-token', self::BASE_URL));

        $form = $crawler->selectButton('Submit')->form([
            'update_password[password][first]' => 'darty1234',
            'update_password[password][second]' => 'darty12345',
        ], Request::METHOD_POST);

        $client->submit($form);

        self::assertAnySelectorTextContains('div.alert', 'Something went wrong during the update of your password.');
        self::assertSelectorTextContains('body', 'Your new password does not match the repeated password.');
    }

    public function testValidationFailWhenPasswordsIsNotLongEnough(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, \sprintf('%s?updateToken=this-is-not-a-token', self::BASE_URL));

        $form = $crawler->selectButton('Submit')->form([
            'update_password[password][first]' => 'darty1234',
            'update_password[password][second]' => 'darty1234',
        ], Request::METHOD_POST);

        $client->submit($form);

        self::assertAnySelectorTextContains('div.alert', 'Something went wrong during the update of your password.');
        self::assertSelectorTextContains('body', 'The new password must be 15 characters or more.');
    }

    public function testValidationFailWhenPasswordWasAlreadyUsed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, \sprintf('%s?updateToken=this-is-not-a-token', self::BASE_URL));

        $form = $crawler->selectButton('Submit')->form([
            'update_password[password][first]' => 'P@ssw0rd15chars',
            'update_password[password][second]' => 'P@ssw0rd15chars',
        ], Request::METHOD_POST);

        $client->submit($form);

        self::assertSelectorTextContains('body', 'Your password must be different than the last 3 previous ones');
    }

    public function testPasswordIsNotUpdatedWhenTokenIsNotValid(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, \sprintf('%s?updateToken=this-is-not-a-token', self::BASE_URL));

        $form = $crawler->selectButton('Submit')->form([
            'update_password[password][first]' => 'P@ssw0rd15charsNew',
            'update_password[password][second]' => 'P@ssw0rd15charsNew',
        ], Request::METHOD_POST);

        $client->submit($form);

        self::assertAnySelectorTextContains('div.alert', 'Token is not valid, your password has not been updated');
    }
}
