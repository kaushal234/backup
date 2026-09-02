<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\SupplierCorrectiveActionRequest;

use App\Controller\SupplierCorrectiveActionRequest\IndexController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

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

    private const BASE_URL = '/supplier-corrective-action-request/';

    public function testIndexControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertResponseRedirectsToLogin();
    }

    public function testIndexControllerInvokeSuccess(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertResponseIsSuccessful();
    }
}
