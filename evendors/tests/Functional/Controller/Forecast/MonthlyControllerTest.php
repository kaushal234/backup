<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Forecast;

use App\Controller\Forecast\MonthlyController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group functional
 *
 * @see MonthlyController
 */
final class MonthlyControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public function provideUris(): iterable
    {
        yield ['/forecast/monthly'];
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
    }
}
