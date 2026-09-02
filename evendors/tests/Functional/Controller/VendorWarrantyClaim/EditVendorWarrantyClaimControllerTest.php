<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\VendorWarrantyClaim;

use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group functional
 *
 * @see EditVendorWarrantyClaimController
 */
final class EditVendorWarrantyClaimControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    private const BASE_URL_NCR = '/vendor-warranty-claim/ncr/show/1';
    private const BASE_URL_WC = '/vendor-warranty-claim/wc/show/1';

    private const FORM_NAME = 'vendor_warranty_claim';

    private const FORM_ACTION_WC = '/vendor-warranty-claim/edit/WC/1';
    private const FORM_ACTION_NCR = '/vendor-warranty-claim/edit/NCR/1';

    private const SUCCESS_LABEL = 'Your VWC has been edited successfully';

    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public function provideVendorWarrantyClaimsActions(): iterable
    {
        yield [self::FORM_ACTION_NCR, self::BASE_URL_NCR];
        yield [self::FORM_ACTION_NCR, self::BASE_URL_NCR];
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsActions
     */
    public function testVWCControllerInvokeUnauthorized(string $formAction): void
    {
        $client = self::createClient();
        $client->request('POST', $formAction);
        self::assertResponseRedirectsToLogin();
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsActions
     */
    public function testVWCControllerInvokeCsrf(string $formAction, string $baseUrl): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('POST', $formAction, [
            self::FORM_NAME.'[supplierCreditAmount]' => '12345$',
            self::FORM_NAME.'[supplierShippingInstruction]' => 'DO it there is not try',
            self::FORM_NAME.'[accepted]' => true,
            self::FORM_NAME.'[shipBackDefectivePart]' => true,
            self::FORM_NAME.'[supplierReturnMerchandiseAuthorization]' => 'I do',
        ]);

        self::assertResponseRedirects($baseUrl);
        self::assertStringNotContainsStringIgnoringCase(self::SUCCESS_LABEL, (string) $client->getResponse());
    }

    public function testVWCControllerWrongModuleFailure(): void
    {
        $client = self::createClient();
        $client->request('POST', '/vendor-warranty-claim/edit/foobar/1');
        self::assertResponseStatusNotFound();
    }

    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public function provideVendorWarrantyClaimsBaseUrls(): iterable
    {
        yield [self::BASE_URL_WC];
        yield [self::BASE_URL_NCR];
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsBaseUrls
     */
    public function testVWCControllerInvokeSuccess(string $baseUrl): void
    {
        $client = $this->editVWC($baseUrl, '456', 'Try hard', 'I authorize that');
        self::assertStringContainsStringIgnoringCase(self::SUCCESS_LABEL, (string) $client->getResponse());
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsBaseUrls
     */
    public function testVWCControllerInvokeFailure(string $baseUrl): void
    {
        $client = $this->editVWC($baseUrl, '', 'Try hard', 'I authorize that');
        self::assertStringContainsStringIgnoringCase('Supplier credit amount must not be blank', (string) $client->getResponse());
    }

    private function editVWC(string $baseUrl, string $supplierCreditAmount, string $supplierShippingInstruction, ?string $supplierReturnMerchandiseAuthorization): KernelBrowser
    {
        $client = self::createClient();
        $this::loginUser($client);

        // access the supplier corrective action form
        $crawler = $client->request('GET', $baseUrl);
        self::assertResponseIsSuccessful();

        // submit with some value
        $addButton = $crawler->selectButton(self::FORM_NAME.'_new_submit');

        $form = $addButton->form();
        $client->submit($form, [
            self::FORM_NAME.'[supplierCreditAmount]' => $supplierCreditAmount,
            self::FORM_NAME.'[supplierShippingInstruction]' => $supplierShippingInstruction,
            self::FORM_NAME.'[accepted]' => true,
            self::FORM_NAME.'[shipBackDefectivePart]' => true,
            self::FORM_NAME.'[supplierReturnMerchandiseAuthorization]' => $supplierReturnMerchandiseAuthorization,
        ]);
        self::assertResponseRedirects($baseUrl);

        $client->followRedirect();

        return $client;
    }
}
