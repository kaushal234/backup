<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Document;

use App\Controller\Document\IndexController;
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

    public function testIndexControllerInvokeSuccess(): void
    {
        $client = self::createClient();
        $this->loginUser($client);
        $client->request('GET', '/document/');
        self::assertResponseIsSuccessful();
    }

    public function testIndexControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', '/document/');
        self::assertResponseRedirectsToLogin();
    }
}
