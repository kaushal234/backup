<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Forecast\Download;

use App\Controller\Forecast\Download\DetailedController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function sprintf;

/**
 * @group functional
 *
 * @see DetailedController
 */
final class DetailedControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public function provideUris(): iterable
    {
        yield ['/forecast/download/detailed/xlsx', 'xlsx'];
    }

    /**
     * As an anonymous user we can"t access secured pages.
     *
     * @dataProvider provideUris
     */
    public function testIsSecured(string $url): void
    {
        $client = self::createClient();
        $client->request('GET', $url);
        self::assertResponseRedirectsToLogin();
    }

    /**
     * As an anonymous user we can"t access secured pages.
     *
     * @dataProvider provideUris
     */
    public function testInvoke(string $url): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', $url);

        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertResponseHeaderSame('content-disposition', sprintf('%s; filename=forecast-detailed.xlsx', 'inline'));
        self::assertTrue($client->getResponse() instanceof BinaryFileResponse);
    }

    /**
     * We can't access a unsupported format and there is not error 500.
     */
    public function testUnknownFormat(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('GET', '/forecast/download/detailed/foobar');
        self::assertResponseStatusNotFound();
    }
}
