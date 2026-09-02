<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\PurchaseOrder\Download;

use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @group functional
 *
 * @see GenerateLabelsController
 */
final class GenerateLabelsControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use KernelBrowserTrait;

    private const BASE_URL = '/purchase-order/show/1/1';
    private const FORM_NAME = 'confirm_label';

    public function testPDF(): void
    {
        $data = [
            'confirm_label[lines][0][lineIdentifier]' => '1',
            'confirm_label[lines][0][partNumber]' => 'B12',
            'confirm_label[lines][0][quantityLabel]' => 6,
            'confirm_label[lines][0][labelDeliveredQuantity]' => 14,
            'confirm_label[lines][0][packingSlip]' => 'tout plein de slip',
        ];

        $client = self::createClient();
        $this::loginUser($client);

        // access the form
        $crawler = $client->request('GET', self::BASE_URL);
        self::assertResponseIsSuccessful();

        $addButton = $crawler->selectButton(self::FORM_NAME.'_action_confirm');

        $form = $addButton->form();
        $client->submit($form, $data);

        self::assertResponseIsSuccessful();
        self::assertInstanceOf(StreamedResponse::class, $client->getResponse());
    }

    public function testGenerateLabelsControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('GET', self::BASE_URL);
        self::assertResponseRedirectsToLogin();
    }
}
