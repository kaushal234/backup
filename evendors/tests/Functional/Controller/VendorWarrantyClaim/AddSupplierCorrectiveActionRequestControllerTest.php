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
 * @see AddSupplierCorrectiveActionRequestController
 */
final class AddSupplierCorrectiveActionRequestControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    private const BASE_URL_NCR = '/vendor-warranty-claim/ncr/show/1';

    private const FORM_NAME = 'supplier_corrective_action_request';

    private const FORM_ACTION_NCR = '/vendor-warranty-claim/supplier-corrective-action-request/add/NCR/1';

    private const SUCCESS_LABEL = 'Your SCAR has been added successfully';

    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public function provideVendorWarrantyClaimsActions(): iterable
    {
        yield [self::FORM_ACTION_NCR, self::BASE_URL_NCR];
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsActions
     */
    public function testSCARControllerInvokeUnauthorized(string $formAction): void
    {
        $client = self::createClient();
        $client->request('POST', $formAction);
        self::assertResponseRedirectsToLogin();
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsActions
     */
    public function testSCARControllerInvokeCsrf(string $formAction, string $baseUrl): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('POST', $formAction, [
            self::FORM_NAME.'[issueOrigin]' => 'Broken heart',
            self::FORM_NAME.'[correctiveAction]' => 'Hug',
            self::FORM_NAME.'[description]' => 'blabla',
            self::FORM_NAME.'[shortDescription]' => 'bla',
            self::FORM_NAME.'[comment]' => 'I will cost 15$',
            self::FORM_NAME.'[_csrf]' => 'foo',
        ]);

        self::assertResponseRedirects($baseUrl);
        self::assertStringNotContainsStringIgnoringCase(self::SUCCESS_LABEL, (string) $client->getResponse());
    }

    public function testSCARControllerWrongModuleFailure(): void
    {
        $client = self::createClient();
        $client->request('POST', '/vendor-warranty-claim/supplier-corrective-action-request/add/foobar/1');
        self::assertResponseStatusNotFound();
    }

    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public function provideVendorWarrantyClaimsBaseUrls(): iterable
    {
        yield [self::BASE_URL_NCR];
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsBaseUrls
     */
    public function testSCARControllerInvokeSuccess(string $baseUrl): void
    {
        $client = $this->submitSCAR($baseUrl, 'Break the engine', 'Use glue', 'description', 'desc', 'glue is sticky');
        self::assertStringContainsStringIgnoringCase(self::SUCCESS_LABEL, (string) $client->getResponse());
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsBaseUrls
     */
    public function testSCARControllerInvokeFailure(string $baseUrl): void
    {
        $client = $this->submitSCAR($baseUrl, '', 'Use glue', 'description', 'desc', 'glue is sticky');
        self::assertStringContainsStringIgnoringCase('Root Cause must not be blank.', (string) $client->getResponse());
    }

    private function submitSCAR(string $baseUrl, string $issueOrigin, string $correctiveAction, string $description, string $shortDescription, ?string $comment): KernelBrowser
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
            self::FORM_NAME.'[issueOrigin]' => $issueOrigin,
            self::FORM_NAME.'[correctiveAction]' => $correctiveAction,
            self::FORM_NAME.'[description]' => $description,
            self::FORM_NAME.'[shortDescription]' => $shortDescription,
            self::FORM_NAME.'[comment]' => $comment,
        ]);
        self::assertResponseRedirects($baseUrl);

        $client->followRedirect();

        return $client;
    }
}
