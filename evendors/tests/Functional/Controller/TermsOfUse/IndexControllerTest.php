<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\TermsOfUse;

use App\Controller\RequestForQuotation\IndexController;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group functional
 *
 * @see IndexController
 */
final class IndexControllerTest extends WebTestCase
{
    public function testIndexControllerInvokeSuccess(): void
    {
        $client = self::createClient();
        $client->request('GET', '/terms-of-use/');
        self::assertResponseIsSuccessful();
    }
}
