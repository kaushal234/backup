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
 * @see ShowNCRController::__invoke
 */
final class ShowNCRControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    public function testShowNCRControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', '/vendor-warranty-claim/ncr/show/1');
        self::assertResponseRedirectsToLogin();
    }

    public function testShowNCRControllerInvokeNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', '/vendor-warranty-claim/ncr/show/404');
    }

    public function testShowNCRControllerInvokeSuccess(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', '/vendor-warranty-claim/ncr/show/1');
        self::assertResponseIsSuccessful();
    }
}
