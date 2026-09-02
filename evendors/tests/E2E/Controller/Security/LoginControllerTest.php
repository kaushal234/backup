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

    /**
     * Shortcut to log a user in a panther client.
     */
    public function testLoginUser(): void
    {
        $identifier = 'empty-name@tld-europe.com';
        $password = 'P@ssw0rd15chars';

        $client = self::createPantherClient();
        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertSelectorTextSame('p', 'Please Login');

        $form = $crawler->selectButton('Continue')->form(['_identifier' => $identifier, '_password' => $password], Request::METHOD_POST);
        $form['terms_of_use_agreement']->tick();

        $client->submit($form);
        $client->takeScreenshot('var/cache/test/test.jpg');

        self::assertPathEquals('/security/login');
        self::assertSelectorTextContains('div.alert', 'Something went wrong, please retry later or contact your representative for support');
    }
}
