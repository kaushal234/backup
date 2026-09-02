<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\TechnicianOnCall;

use App\Controller\TechnicianOnCall\IndexController;
use App\Test\Helpers\PantherBrowserTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see IndexController
 */
final class AddFileControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;

    private const BASE_URL = '/technician-on-calls/44/files/add';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testTechnicianOnCallPageRedirectsWhenNonAuthenticated(): void
    {
        $client = self::createPantherClient();
        $client->request(Request::METHOD_GET, self::BASE_URL);

        self::assertPathEquals('/security/login');
        self::assertPageTitleSame('Login - Extranet');
    }

    public function testAddFile(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $crawler = $client->request(Request::METHOD_GET, self::BASE_URL);

        $form = $crawler->selectButton('Add')->form([
            'file[file]' => __DIR__.'/../../Fixtures/Files/image.jpg',
        ], Request::METHOD_POST);

        $client->submit($form);
        $crawler = $client->getCrawler();

        $client->waitFor('div.alert');
        self::assertSelectorTextContains('div.alert', 'File has been added successfully.');
    }
}
