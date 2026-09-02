<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Messenger;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operations;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\User;
use App\EventListener\Messenger\JiraSyncFailedMessageListener;
use App\Factory\Service\TechnicianOnCallJiraCommentSyncFailureCommentFactory;
use App\Factory\Service\TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory;
use App\Jira\DataProvider\JiraItemDataProvider;
use App\Jira\Resources\TracteasyIssueType;
use App\Message\Service\TechnicianOnCallJiraCommentSync;
use App\Message\Service\TechnicianOnCallJiraTracteasyIssueCreate;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

class JiraSyncFailedMessageListenerTest extends TestCase
{
    public function testDoesNothingWhenTheMessageWillStillBeRetried(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');

        $listener = new JiraSyncFailedMessageListener(
            $this->createMock(IriConverterInterface::class),
            $entityManager,
            $this->createMock(TechnicianOnCallJiraCommentSyncFailureCommentFactory::class),
            $this->createMock(TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory::class),
            $this->resourceMetadataFactory(),
            $this->createMock(JiraItemDataProvider::class),
        );

        $event = new WorkerMessageFailedEvent(new Envelope(new TechnicianOnCallJiraCommentSync('/comments/1')), 'async', new \RuntimeException('boom'));
        $event->setForRetry();

        $listener($event);
    }

    public function testPersistsAFailureCommentWhenACommentSyncFinallyFails(): void
    {
        $comment = new Comment();
        $comment->setResource('/service/technician_on_calls/7');
        $technicianOnCall = new TechnicianOnCall();

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getResourceFromIri')->willReturnMap([
            ['/comments/1', [], null, $comment],
            ['/service/technician_on_calls/7', [], null, $technicianOnCall],
        ]);

        $exception = new \RuntimeException('Jira is down');
        $failureComment = new Comment();

        $commentSyncFailureFactory = $this->createMock(TechnicianOnCallJiraCommentSyncFailureCommentFactory::class);
        $commentSyncFailureFactory->expects(self::once())
            ->method('create')
            ->with($technicianOnCall, $comment, $exception)
            ->willReturn($failureComment);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with($failureComment);
        $entityManager->expects(self::once())->method('flush');

        $listener = new JiraSyncFailedMessageListener(
            $iriConverter,
            $entityManager,
            $commentSyncFailureFactory,
            $this->createMock(TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory::class),
            $this->resourceMetadataFactory(),
            $this->createMock(JiraItemDataProvider::class),
        );

        $listener(new WorkerMessageFailedEvent(new Envelope(new TechnicianOnCallJiraCommentSync('/comments/1')), 'async', $exception));
    }

    public function testPersistsAFailureCommentWhenIssueCreationFinallyFails(): void
    {
        $technicianOnCall = new TechnicianOnCall();
        $user = new User();
        $issueType = new TracteasyIssueType();

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getResourceFromIri')->willReturnMap([
            ['/service/technician_on_calls/7', [], null, $technicianOnCall],
            ['/directory/people/1', [], null, $user],
        ]);

        $issueTypeProvider = $this->createMock(JiraItemDataProvider::class);
        $issueTypeProvider->method('provide')->willReturn($issueType);

        $exception = new \RuntimeException('Jira is down');
        $failureComment = new Comment();

        $issueCreateFailureFactory = $this->createMock(TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory::class);
        $issueCreateFailureFactory->expects(self::once())
            ->method('create')
            ->with($technicianOnCall, $user, $issueType, $exception)
            ->willReturn($failureComment);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with($failureComment);
        $entityManager->expects(self::once())->method('flush');

        $listener = new JiraSyncFailedMessageListener(
            $iriConverter,
            $entityManager,
            $this->createMock(TechnicianOnCallJiraCommentSyncFailureCommentFactory::class),
            $issueCreateFailureFactory,
            $this->resourceMetadataFactory(),
            $issueTypeProvider,
        );

        $listener(new WorkerMessageFailedEvent(new Envelope(new TechnicianOnCallJiraTracteasyIssueCreate('/service/technician_on_calls/7', '/directory/people/1')), 'async', $exception));
    }

    private function resourceMetadataFactory(): ResourceMetadataCollectionFactoryInterface
    {
        $operation = new Get(class: TracteasyIssueType::class);
        $apiResource = (new ApiResource())->withOperations(new Operations([$operation]));
        $collection = new ResourceMetadataCollection(TracteasyIssueType::class, [$apiResource]);

        $factory = $this->createMock(ResourceMetadataCollectionFactoryInterface::class);
        $factory->method('create')->with(TracteasyIssueType::class)->willReturn($collection);

        return $factory;
    }
}
