<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Controller\PhotoController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

use function Symfony\Component\String\u;

/**
 * @group functional
 *
 * @see PhotoController
 */
final class PhotoControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    private const BASE_URL = '/people/1/photo/1';

    public function testPhotoControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', self::BASE_URL);
        self::assertResponseRedirectsToLogin();
    }

    public function testPhotoControllerInvokeNotFound(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', u(self::BASE_URL)->replace('/1', '/404')->toString());
        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertResponseHeaderSame('content-disposition', 'inline; filename=no_photo_catalogue.png');
        self::assertResponseHeaderSame('content-type', 'image/png');
        self::assertTrue($client->getResponse() instanceof StreamedResponse);
    }

    public function testPhotoControllerInvokeSuccess(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', self::BASE_URL);
        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertTrue($client->getResponse() instanceof StreamedResponse);
    }
}
