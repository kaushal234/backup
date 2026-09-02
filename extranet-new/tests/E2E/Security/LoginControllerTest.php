<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Security;

use App\Controller\Security\LoginController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see LoginController
 */
final class LoginControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/security/login';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testLoginUserDoesNotWorkWithWrongPassword(): void
    {
        $identifier = 'empty-name@tld-europe.com';
        $password = 'P@ssw0rd15chars';

        $client = self::createPantherClient();
        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);

        $form = $crawler->selectButton('Continue')->form(['_identifier' => $identifier, '_password' => $password], Request::METHOD_POST);

        $client->submit($form);

        self::assertPathEquals('/security/login');
        self::assertSelectorTextContains('div.login-error', 'Invalid credentials.');
    }

    public function testLoginUserDoesNotWorkWithoutAcls(): void
    {
        $identifier = 'dounot.use@tld.com';
        $password = 'P@ssw0rd15chars';

        $client = self::createPantherClient();
        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);

        $form = $crawler->selectButton('Continue')->form(['_identifier' => $identifier, '_password' => $password], Request::METHOD_POST);

        $client->submit($form);

        self::assertPathEquals('/security/login');

        $client->waitFor('div.login-error');

        self::assertSelectorTextContains('div.login-error', 'Your account is activated but not set up correctly, please ask your Alvest representative.');
    }
}
