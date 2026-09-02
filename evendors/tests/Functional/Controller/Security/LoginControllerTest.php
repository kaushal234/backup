<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Security;

use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group functional
 *
 * @see LoginController
 */
final class LoginControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    public function testLoginFailsWhenAccountIsDisabled(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/security/login');
        $form = $crawler->selectButton('Continue')->form();
        $client->submit($form, [
            '_identifier' => 'dummy',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/security/login');
        $client->followRedirect();

        self::assertSelectorTextContains('body', 'Please Login Invalid credentials.');
    }

    public function testLoginFailsForInvalidCredentials(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/security/login');
        $form = $crawler->selectButton('Continue')->form();
        $client->submit($form, [
            '_identifier' => 'azjezz',
            '_password' => 'incorrect-password',
        ]);

        self::assertResponseRedirects('/security/login');
        $client->followRedirect();

        self::assertSelectorTextContains('body', 'Please Login Invalid credentials.');
    }

    public function testLoginFailsWhenUserIsNotFound(): void
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/security/login');
        $form = $crawler->selectButton('Continue')->form();
        $client->submit($form, [
            '_identifier' => 'unknown',
            '_password' => '123456789',
        ]);

        self::assertResponseRedirects('/security/login');
        $client->followRedirect();

        self::assertSelectorTextContains('body', 'Please Login Invalid credentials.');
    }

    public function testLoginWhenAlreadyLogged(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', '/security/login');
        self::assertResponseRedirects('/');
    }
}
