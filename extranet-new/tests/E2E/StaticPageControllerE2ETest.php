<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller;

use App\Controller\StaticPageController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see StaticPageController
 */
final class StaticPageControllerE2ETest extends PantherTestCase
{
    use PantherBrowserTrait;

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    /**
     * @dataProvider providePageTitles
     */
    public function testPageLoadsWithCorrectTitle(string $url, string $expectedTitle): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, $url);

        self::assertPathEquals($url);
        self::assertPageTitleSame($expectedTitle);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function providePageTitles(): iterable
    {
        yield 'technical services' => ['/services/technical-services',       'Technical Services'];
        yield 'trainings' => ['/services/trainings',                 'Technical Trainings'];
        yield 'major repairs' => ['/services/major-components-repairs',  'Major components repairs'];
        yield 'warranty conditions' => ['/services/warranty-conditions',       'Warranty Conditions'];
        yield 'service bulletins' => ['/services/service-bulletins',         'Service Bulletins'];
        yield 'telemetry' => ['/services/telemetry',                 'Monitor your fleet with telemetry'];
        yield 'parts terms' => ['/parts/terms-and-conditions',         'Parts Terms & Conditions'];
        yield 'parts returns' => ['/parts/return-instructions',          'Parts return instructions'];
        yield 'rspl' => ['/parts/recommended-spare-parts-list', 'Recommended Spare Parts Lists'];
        yield 'coming soon' => ['/coming-soon',                        'Coming soon'];
        yield 'accessories' => ['/parts/accessories',                  'Accessories'];
    }
}
