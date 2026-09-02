<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\Manufacturing;

use App\Controller\Manufacturing\BillOfMaterialDrawing3DFilesController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see BillOfMaterialDrawing3DFilesController
 */
final class BillOfMaterialDrawing3DFilesControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    /**
     * Non-authenticated users are redirected to the login page.
     */
    public function testRedirectsToLoginWhenUserIsNotAuthenticated(): void
    {
        $client = self::createPantherClient();

        $client->request(
            Request::METHOD_GET,
            '/manufacturing/bill-of-material-drawing-3d-files/500/6500171/1989-12-31T23:00:00+00:00/B'
        );

        self::assertPathEquals('/security/login');
    }

    /**
     * Authenticated user reaches the page but the API rejects the request
     * (ZIP missing, invalid or exceeding allowed size).
     *
     * In this case, an error flash message is displayed and the user is redirected to the homepage.
     */
    public function testShowsErrorMessageWhenApiRejects3DFilesRequest(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(
            Request::METHOD_GET,
            '/manufacturing/bill-of-material-drawing-3d-files/400/1049951/2022-11-02T17:13:00Z/B'
        );
        $client->takeScreenshot('test4.png');

        self::assertSelectorTextContains('div.alert.alert-dismissible.alert-danger', 'The 3D ZIP file for part "1049951" (revision "B", site 400) could not be found or exceeds the maximum allowed size of 500 MB. Please contact your buyer to report this issue.');
        self::assertPathEquals('/');
    }

    public function testShowsErrorMessageWhenApiRejects3DFilesRequest2(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(
            Request::METHOD_GET,
            '/manufacturing/bill-of-material-drawing-3d-files/400/1049951/2022-11-02T17:13:00Z/'
        );
        $client->takeScreenshot('test5.png');

        self::assertSelectorTextContains('div.alert.alert-dismissible.alert-danger', 'The 3D ZIP file for part "1049951" (revision "N/A", site 400) could not be found or exceeds the maximum allowed size of 500 MB. Please contact your buyer to report this issue.');
        self::assertPathEquals('/');
    }

    public function testShowsErrorMessageWhenApiRejects3DFilesRequest3(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(
            Request::METHOD_GET,
            '/manufacturing/bill-of-material-drawing-3d-files/400/1049951/2022-11-02T17:13:00Z/'
        );
        $client->takeScreenshot('test6.png');

        self::assertSelectorTextContains('div.alert.alert-dismissible.alert-danger', 'The 3D ZIP file for part "1049951" (revision "N/A", site 400) could not be found or exceeds the maximum allowed size of 500 MB. Please contact your buyer to report this issue.');
        self::assertPathEquals('/');
    }

    /**
     * Invalid request parameters lead to a generic error message
     * (no ZIP download and no technical details exposed to the user).
     */
    public function testShowsGenericErrorMessageWhenRequestParametersAreInvalid(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(
            Request::METHOD_GET,
            '/manufacturing/bill-of-material-drawing-3d-files/400/104Jesaispasquoi/2022-11-02T17:13:00/B'
        );

        $pageSource = $client->getPageSource();

        $this->assertStringNotContainsString('application/zip', $pageSource);

        self::assertSelectorTextContains('div', 'Something went wrong, please retry later or contact your representative for support');
    }

    /**
     * Successful ZIP downloads cannot be reliably asserted with Panther
     * because WebDriver does not expose binary responses or download events.
     *
     * This scenario is covered by API tests instead.
     */
    public function test3DFilesZipDownloadIsHandledByApiTestsOnly(): void
    {
        self::markTestSkipped(
            'ZIP download success is covered by API tests; Panther cannot assert binary downloads.'
        );
    }
}
