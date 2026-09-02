<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler\Service;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operations;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;
use App\Jira\DataProcessor\JiraDataProcessor;
use App\Jira\Resources\JiraIssueComment;
use App\Message\Service\TechnicianOnCallJiraCommentSync;
use App\MessageHandler\Service\TechnicianOnCallJiraCommentSyncHandler;
use PHPUnit\Framework\TestCase;

class TechnicianOnCallJiraCommentSyncHandlerTest extends TestCase
{
    public function testPushesThePublicCommentToJiraWhenTocHasAnIssueKey(): void
    {
        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->jiraTracteasyIssueKey = 'AIRB-42';

        $comment = new Comment();
        $comment->setMessage('<p>Hello</p>');
        $comment->setResource('/service/technician_on_calls/7');

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getResourceFromIri')->willReturnMap([
            ['/comments/1', [], null, $comment],
            ['/service/technician_on_calls/7', [], null, $technicianOnCall],
        ]);

        $processor = $this->createMock(JiraDataProcessor::class);
        $processor->expects(self::once())
            ->method('process')
            ->with(self::callback(static function (JiraIssueComment $issueComment) {
                self::assertSame('AIRB-42', $issueComment->getIssueKey());
                self::assertSame('<p>Hello</p>', $issueComment->body);
                self::assertTrue($issueComment->public);

                return true;
            }));

        $handler = new TechnicianOnCallJiraCommentSyncHandler($iriConverter, $this->resourceMetadataFactory(), $processor);

        $handler(new TechnicianOnCallJiraCommentSync('/comments/1'));
    }

    public function testPushesAPrivateCommentToJiraWithPublicFalse(): void
    {
        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->jiraTracteasyIssueKey = 'AIRB-42';

        $comment = new Comment();
        $comment->setMessage('<p>Internal note</p>');
        $comment->setResource('/service/technician_on_calls/7');
        $comment->setPublic(false);

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getResourceFromIri')->willReturnMap([
            ['/comments/1', [], null, $comment],
            ['/service/technician_on_calls/7', [], null, $technicianOnCall],
        ]);

        $processor = $this->createMock(JiraDataProcessor::class);
        $processor->expects(self::once())
            ->method('process')
            ->with(self::callback(static function (JiraIssueComment $issueComment) {
                self::assertFalse($issueComment->public);

                return true;
            }));

        $handler = new TechnicianOnCallJiraCommentSyncHandler($iriConverter, $this->resourceMetadataFactory(), $processor);

        $handler(new TechnicianOnCallJiraCommentSync('/comments/1'));
    }

    public function testDoesNothingWhenTocHasNoJiraIssueKey(): void
    {
        $technicianOnCall = new TechnicianOnCall();

        $comment = new Comment();
        $comment->setMessage('<p>Hello</p>');
        $comment->setResource('/service/technician_on_calls/7');

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getResourceFromIri')->willReturnMap([
            ['/comments/1', [], null, $comment],
            ['/service/technician_on_calls/7', [], null, $technicianOnCall],
        ]);

        $processor = $this->createMock(JiraDataProcessor::class);
        $processor->expects(self::never())->method('process');

        $handler = new TechnicianOnCallJiraCommentSyncHandler($iriConverter, $this->resourceMetadataFactory(), $processor);

        $handler(new TechnicianOnCallJiraCommentSync('/comments/1'));
    }

    private function resourceMetadataFactory(): ResourceMetadataCollectionFactoryInterface
    {
        $operation = new Post(name: 'jira_tracteasy_post_comment', class: JiraIssueComment::class);
        $apiResource = (new ApiResource())->withOperations(new Operations(['jira_tracteasy_post_comment' => $operation]));
        $collection = new ResourceMetadataCollection(JiraIssueComment::class, [$apiResource]);

        $factory = $this->createMock(ResourceMetadataCollectionFactoryInterface::class);
        $factory->method('create')->with(JiraIssueComment::class)->willReturn($collection);

        return $factory;
    }
}
