<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\PurchaseOrder;

use App\Controller\PurchaseOrder\ShowController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @group functional
 *
 * @see ShowController
 */
final class ShowControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    public function testShowControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', '/purchase-order/show/1/1');

        self::assertResponseRedirectsToLogin();
    }

    public function testShowControllerInvokeFailureNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', '/purchase-order/show/404/1');
    }

    public function testShowControllerInvokeSuccess(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', '/purchase-order/show/1/1');
        self::assertResponseIsSuccessful();
    }
}
