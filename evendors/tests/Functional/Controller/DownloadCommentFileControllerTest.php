<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function Symfony\Component\String\u;

/**
 * @group functional
 *
 * @see DownloadCommentFileController
 */
final class DownloadCommentFileControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    private const BASE_URL = '/comments/1/file/1';

    public function testDownloadCommentFileControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', self::BASE_URL);
        self::assertResponseRedirectsToLogin();
    }

    public function testDownloadCommentFileControllerInvokeNotFound(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', u(self::BASE_URL)->replace('/1', '/404')->toString());
        self::assertResponseStatusNotFound();
    }

    public function testDownloadCommentFileControllerInvokeSuccess(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', self::BASE_URL);
        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertTrue($client->getResponse() instanceof BinaryFileResponse);
    }
}
