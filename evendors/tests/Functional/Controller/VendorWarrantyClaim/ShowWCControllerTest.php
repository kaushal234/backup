<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\VendorWarrantyClaim;

use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group functional
 *
 * @see ShowWCController
 */
final class ShowWCControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    public function testShowWCControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', '/vendor-warranty-claim/wc/show/1');
        self::assertResponseRedirectsToLogin();
    }

    public function testShowWCControllerInvokeNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', '/vendor-warranty-claim/wc/show/404');
    }

    public function testShowWCControllerInvokeSuccess(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', '/vendor-warranty-claim/wc/show/1');
        self::assertResponseIsSuccessful();
    }
}
