<?php

declare(strict_types=1);

namespace App\Tests\Factory\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\User;
use App\Factory\Service\TechnicianOnCallJiraCommentSyncFailureCommentFactory;
use PHPUnit\Framework\TestCase;

class TechnicianOnCallJiraCommentSyncFailureCommentFactoryTest extends TestCase
{
    public function testCreateBuildsAPrivateCommentAttributedToTheSyncedCommentAuthor(): void
    {
        $technicianOnCall = new TechnicianOnCall();
        $author = new User();

        $syncedComment = new Comment();
        $syncedComment->setResource('/service/technician_on_calls/7');
        $syncedComment->setUser($author);

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getIriFromResource')->with($technicianOnCall)->willReturn('/service/technician_on_calls/7');

        $factory = new TechnicianOnCallJiraCommentSyncFailureCommentFactory($iriConverter);

        $failureComment = $factory->create($technicianOnCall, $syncedComment, new \RuntimeException('Jira is down'));

        self::assertFalse($failureComment->isPublic());
        self::assertSame(TechnicianOnCall::MODULE_NAME, $failureComment->discriminator);
        self::assertSame('/service/technician_on_calls/7', $failureComment->getResource());
        self::assertSame($author, $failureComment->getUser());
        self::assertStringContainsString('Jira is down', $failureComment->getMessage());
    }
}
