<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Document;

use App\Controller\Document\DownloadController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @group functional
 *
 * @see DownloadController
 */
final class DownloadControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    public function testDownloadControllerInvokeSuccess2(): void
    {
        $client = self::createClient();
        $this->loginUser($client);
        $client->request('GET', '/document/download/1');
        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertTrue($client->getResponse() instanceof BinaryFileResponse);
    }

    public function testDownloadControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', '/document/download/1');
        self::assertResponseRedirectsToLogin();
    }

    public function testDownloadControllerInvokeNotFound(): void
    {
        $client = self::createClient();
        $this->loginUser($client);
        $client->request('GET', '/document/download/404');
        self::assertResponseStatusNotFound();
    }
}
