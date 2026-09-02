<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\IndexController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group functional
 *
 * @see IndexController
 */
final class IndexControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    public function testNonAuthenticated(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');
        self::assertResponseRedirectsToLogin();
        $client->followRedirect();
        self::assertSelectorTextContains('body', 'Please Login');
    }

    public function testAuthenticated(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', '/');
        self::assertResponseIsSuccessful();
    }
}
