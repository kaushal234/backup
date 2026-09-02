<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Forecast\Download;

use App\Controller\Forecast\Download\MonthlyController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function sprintf;

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

    public function testXlsx(): void
    {
        $client = self::createClient();
        $this::loginUser($client);

        $client->request('GET', '/forecast/download/monthly/xlsx');

        self::assertResponseIsSuccessful();
        self::assertResponseHasHeader('content-disposition');
        self::assertResponseHeaderSame('content-disposition', sprintf('%s; filename=forecast-monthly.xlsx', 'inline'));
        self::assertTrue($client->getResponse() instanceof BinaryFileResponse);
    }
}
