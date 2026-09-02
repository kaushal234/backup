<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Security\PasswordReset;

use App\Controller\Security\PasswordReset\IndexController;
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
    private const BASE_URL = '/security/password-reset';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testRouteIsPublic(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals(self::BASE_URL);
    }

    public function testEmailIsSent(): void
    {
        $client = self::createPantherClient();
        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals(self::BASE_URL);

        $form = $crawler->selectButton('Continue')->form([
            'password_reset_request[email]' => 'julien.lepers@tld.com',
        ], Request::METHOD_POST);

        $client->submit($form);

        self::assertPathEquals(self::BASE_URL);
        self::assertSelectorTextContains('div.alert', 'Success! An email has been sent that contains a link that you can click to reset your password.');
    }
}
