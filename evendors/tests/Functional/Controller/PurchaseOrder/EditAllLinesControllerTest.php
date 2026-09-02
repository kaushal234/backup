<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\PurchaseOrder;

use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group functional
 *
 * @see EditAllLineController
 */
final class EditAllLinesControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    private const BASE_URL = '/purchase-order/show/1/1';
    private const BASE_EDIT = '/purchase-order/edit_all_delivery_date/1/1';

    private const FORM_NAME = 'edit_all_lines';

    /**
     * @see templates/purchase-order/components/modal/line.html.twig
     */
    public function testEditAllLineControllerInvokeSuccess(): void
    {
        $data = [
            'edit_all_lines[editLines][0][confirmedSupplierDate]' => '2029-10-02',
            self::FORM_NAME.'[message]' => 'I am sure to confirm',
        ];

        $client = self::createClient();
        $this::loginUser($client);

        // access the form
        $crawler = $client->request('GET', self::BASE_URL);
        self::assertResponseIsSuccessful();

        $addButton = $crawler->selectButton(self::FORM_NAME.'_action_confirm');

        $form = $addButton->form();
        $client->submit($form, $data);

        self::assertResponseRedirects(self::BASE_URL);
        $client->followRedirect();
        self::assertStringContainsStringIgnoringCase('<span>Delivery date has been changed successfully.</span>', (string) $client->getResponse());
    }

    /**
     * @see templates/purchase-order/components/modal/line.html.twig
     */
    public function testEditLineWithEmptyDateCreateAnError(): void
    {
        $data = [
            'edit_all_lines[editLines][0][confirmedSupplierDate]' => '',
            self::FORM_NAME.'[message]' => 'I am sure to confirm',
        ];

        $client = self::createClient();
        $this::loginUser($client);

        // access the form
        $crawler = $client->request('GET', self::BASE_URL);
        self::assertResponseIsSuccessful();

        $addButton = $crawler->selectButton(self::FORM_NAME.'_action_confirm');

        $form = $addButton->form();
        $client->submit($form, $data);

        self::assertStringContainsStringIgnoringCase('Invalid date : Confirmed delivery date has to be', (string) $client->getResponse());
        self::assertStringContainsStringIgnoringCase('Greater than today, please.', (string) $client->getResponse());
    }

    /**
     * @see templates/purchase-order/components/modal/line.html.twig
     */
    public function testEditLineWithBadDateCreateAnError(): void
    {
        $badDate = new DateTime('now');
        $badDate->modify('-1 day');
        $data = [
            'edit_all_lines[editLines][0][confirmedSupplierDate]' => $badDate->format('Y-m-d'),
            self::FORM_NAME.'[message]' => 'I am sure to confirm',
        ];

        $client = self::createClient();
        $this::loginUser($client);

        // access the form
        $crawler = $client->request('GET', self::BASE_URL);
        self::assertResponseIsSuccessful();

        $addButton = $crawler->selectButton(self::FORM_NAME.'_action_confirm');

        $form = $addButton->form();
        $client->submit($form, $data);

        self::assertStringContainsStringIgnoringCase('Invalid date : Confirmed delivery date has to be', (string) $client->getResponse());
        self::assertStringContainsStringIgnoringCase('Greater than today, please.', (string) $client->getResponse());
    }

    /**
     * @see templates/purchase-order/components/modal/line.html.twig
     */
    public function testEditLineWithBlankDateCreateAnError(): void
    {
        $data = [
            'edit_all_lines[editLines][0][confirmedSupplierDate]' => '',
            self::FORM_NAME.'[message]' => 'I am sure to confirm',
        ];

        $client = self::createClient();
        $this::loginUser($client);

        // access the form
        $crawler = $client->request('GET', self::BASE_URL);
        self::assertResponseIsSuccessful();

        $addButton = $crawler->selectButton(self::FORM_NAME.'_action_confirm');

        $form = $addButton->form();
        $client->submit($form, $data);

        self::assertStringContainsStringIgnoringCase('Invalid date : Confirmed delivery date has to be', (string) $client->getResponse());
        self::assertStringContainsStringIgnoringCase('Greater than today, please.', (string) $client->getResponse());
    }
}
