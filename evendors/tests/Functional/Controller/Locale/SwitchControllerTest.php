<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Locale;

use App\Controller\Locale\SwitchController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * @group functional
 *
 * @see SwitchController
 */
final class SwitchControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    /**
     * @return iterable<array{0: string}>
     */
    public function provideLocalesSuccess(): iterable
    {
        yield ['fr'];
        yield ['en'];
        yield ['zh-CN'];
    }

    /**
     * @dataProvider provideLocalesSuccess
     */
    public function testSwitchControllerSuccess(string $locale): void
    {
        $client = self::createClient();
        $client->request('POST', '/locale/switch', ['locale' => $locale]);
        self::assertResponseIsSuccessful();
        self::assertResponseHasCookie('_locale');
        self::assertResponseCookieValueSame('_locale', $locale);
    }

    /**
     * @return iterable<array{0: string}>
     */
    public function provideLocalesFailure(): iterable
    {
        yield ['foo'];
        yield [''];
    }

    /**
     * @dataProvider provideLocalesFailure
     */
    public function testSwitchControllerFailure(string $locale): void
    {
        $client = self::createClient();
        $client->request('POST', '/locale/switch', ['locale' => $locale]);
        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }
}
