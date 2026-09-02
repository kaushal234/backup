<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\VendorWarrantyClaim;

use App\Controller\VendorWarrantyClaim\IndexController;
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

    private const BASE_URL = '/vendor-warranty-claim/';

    public function testIndexControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', self::BASE_URL);
        self::assertResponseRedirectsToLogin();
    }

    public function testIndexControllerInvokeSuccess(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', self::BASE_URL);
        self::assertResponseIsSuccessful();
    }
}
