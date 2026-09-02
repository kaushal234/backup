<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\SupplierCorrectiveActionRequest;

use App\Controller\SupplierCorrectiveActionRequest\CommentController;
use App\Test\Helpers\BrowserKitAssertionsTrait;
use App\Test\Helpers\ContainerTrait;
use App\Test\Helpers\KernelBrowserTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    private const BASE_URL = '/supplier-corrective-action-request/1';

    private const FORM_NAME = 'comment';

    private const FORM_ACTION = '/supplier-corrective-action-request/comment/1';

    public function testCommentControllerInvokeUnauthorized(): void
    {
        $client = self::createClient();
        $client->request('POST', self::FORM_ACTION);
        self::assertResponseRedirectsToLogin();
    }

    public function testCommentControllerInvokeCsrf(): void
    {
        $client = self::createClient();
        $this::loginUser($client);
        $client->request('POST', self::FORM_ACTION, [
            self::FORM_NAME.'[_csrf]' => 'foo',
        ]);
        self::assertResponseRedirects(self::BASE_URL);
        self::assertStringNotContainsStringIgnoringCase('Your comment has been added successfully', (string) $client->getResponse());
    }

    public function testCommentControllerInvokeNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $client = self::createClient();
        $this::loginUser($client);
        $client->request('POST', '/supplier-corrective-action-request/comment/404');
    }

    public function testCommentControllerInvokeSuccess(): void
    {
        $client = $this->submitComment('Very nice comment');
        self::assertStringContainsStringIgnoringCase('Your comment has been added successfully', (string) $client->getResponse());
    }

    public function testCommentControllerInvokeFailure(): void
    {
        $client = $this->submitComment('');
        self::assertStringContainsStringIgnoringCase('Comment must not be blank', (string) $client->getResponse());
    }

    private function submitComment(string $comment): KernelBrowser
    {
        $client = self::createClient();
        $this::loginUser($client);

        // access the comment form
        $crawler = $client->request('GET', self::BASE_URL);
        self::assertResponseIsSuccessful();

        // submit with comment
        $addButton = $crawler->selectButton(self::FORM_NAME.'_new_submit');
        $form = $addButton->form();
        $client->submit($form, [
            self::FORM_NAME.'[message]' => $comment,
        ]);

        self::assertResponseRedirects(self::BASE_URL);
        $client->followRedirect();

        return $client;
    }
}
