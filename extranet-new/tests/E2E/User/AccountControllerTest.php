<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Contact;

use App\Controller\User\AccountController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see AccountController
 */
final class AccountControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/account';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testAccountPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testAccountPageIsDisplayed(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals(self::BASE_URL);
        self::assertAnySelectorTextContains('a', 'Edit');
        self::assertAnySelectorTextContains('a', 'Update Password');
    }
}
