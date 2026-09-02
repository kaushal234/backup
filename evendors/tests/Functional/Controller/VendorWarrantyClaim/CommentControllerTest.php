<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\VendorWarrantyClaim;

use App\Controller\VendorWarrantyClaim\CommentController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @group functional
 *
 * @see CommentController
 */
final class CommentControllerTest extends WebTestCase
{
    use BrowserKitAssertionsTrait;
    use ContainerTrait;
    use KernelBrowserTrait;

    private const BASE_URL_WC = '/vendor-warranty-claim/wc/show/1';
    private const BASE_URL_NCR = '/vendor-warranty-claim/ncr/show/1';

    private const FORM_NAME = 'comment';

    private const FORM_ACTION_WC = '/vendor-warranty-claim/comment/WC/1';
    private const FORM_ACTION_NCR = '/vendor-warranty-claim/comment/NCR/1';

    private const SUCCESS_LABEL = 'Your comment has been added successfully';

    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public function provideVendorWarrantyClaimsActions(): iterable
    {
        yield [self::FORM_ACTION_WC, self::BASE_URL_WC];
        yield [self::FORM_ACTION_NCR, self::BASE_URL_NCR];
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsActions
     */
    public function testCommentControllerInvokeUnauthorized(string $formAction): void
    {
        $client = self::createClient();
        $client->request('POST', $formAction);
        self::assertResponseRedirectsToLogin();
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsActions
     */
    public function testCommentControllerInvokeCsrf(string $formAction, string $baseUrl): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('POST', $formAction, [
            self::FORM_NAME.'[message]' => 'hacking attempt',
            self::FORM_NAME.'[_csrf]' => 'foo',
        ]);

        self::assertResponseRedirects($baseUrl);
        self::assertStringNotContainsStringIgnoringCase(self::SUCCESS_LABEL, (string) $client->getResponse());
    }

    public function testCommentControllerWrongModuelFailure(): void
    {
        $client = self::createClient();
        $client->request('POST', '/vendor-warranty-claim/comment/foobar/1');
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
    public function testCommentControllerInvokeSuccess(string $baseUrl): void
    {
        $client = $this->submitComment($baseUrl, 'Very nice comment');
        self::assertStringContainsStringIgnoringCase(self::SUCCESS_LABEL, (string) $client->getResponse());
    }

    /**
     * @dataProvider provideVendorWarrantyClaimsBaseUrls
     */
    public function testCommentControllerInvokeFailure(string $baseUrl): void
    {
        $client = $this->submitComment($baseUrl, '');
        self::assertStringContainsStringIgnoringCase('Comment must not be blank', (string) $client->getResponse());
    }

    private function submitComment(string $baseUrl, string $comment): KernelBrowser
    {
        $client = self::createClient();
        $this::loginUser($client);

        // access the comment form
        $crawler = $client->request('GET', $baseUrl);
        self::assertResponseIsSuccessful();

        // submit with some value
        $addButton = $crawler->selectButton(self::FORM_NAME.'_new_submit');
        $form = $addButton->form();
        $client->submit($form, [
            self::FORM_NAME.'[message]' => $comment,
        ]);

        self::assertResponseRedirects($baseUrl);
        $client->followRedirect();

        return $client;
    }
}
