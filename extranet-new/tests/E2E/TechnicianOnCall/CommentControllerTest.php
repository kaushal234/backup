<?php

declare(strict_types=1);

namespace App\Tests\E2E\Controller\TechnicianOnCall;

use App\Controller\TechnicianOnCall\CommentController;
use App\Test\Helpers\PantherBrowserTrait;
use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Panther\PantherTestCase;

/**
 * @group e2e
 *
 * @see CommentController
 */
final class CommentControllerTest extends PantherTestCase
{
    use PantherBrowserTrait;
    private const BASE_URL = '/technician-on-calls/44';

    protected function tearDown(): void
    {
        self::createPantherClient()->quit();

        parent::tearDown();
    }

    public function testCommentCanBeAdded(): void
    {
        $client = self::createPantherClient();
        self::loginUser();

        $client->request(Request::METHOD_GET, self::BASE_URL);
        self::assertPathEquals(self::BASE_URL);

        $client->waitFor('button[data-bs-target="#new_comment_modal"]');
        $client->findElement(WebDriverBy::cssSelector('button[data-bs-target="#new_comment_modal"]'))->click();

        $client->waitForVisibility('#new_comment_modal.show');

        $client->findElement(WebDriverBy::id('comment_message'))->sendKeys('This is a test.');

        $client->findElement(WebDriverBy::cssSelector('#comment_new_submit'))->click();

        $client->waitFor('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'Your comment has been added successfully.');
        $this->assertSelectorTextContains('body', 'This is a test.');
    }

    public function testCommentButtonHiddenForUserWithoutTocRole(): void
    {
        $client = self::createPantherClient();
        // "user-campaign@tld.com" only has the role_ST ACL (no role_TOC) but has equipment access to TOC 7:
        // the page is visible but the "add comment" button must be hidden.
        self::loginUser('user-campaign@tld.com', 'password');

        $client->request(Request::METHOD_GET, '/technician-on-calls/7');
        self::assertPathEquals('/technician-on-calls/7');

        self::assertSelectorNotExists('button[data-bs-target="#new_comment_modal"]');
    }
}
