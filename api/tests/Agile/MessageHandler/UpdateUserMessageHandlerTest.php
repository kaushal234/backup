<?php

declare(strict_types=1);

namespace App\Tests\Agile\MessageHandler;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Agile\Message\UpdateUserMessage;
use App\Agile\MessageHandler\UpdateUserMessageHandler;
use App\Agile\UserSyncContext;
use App\Agile\UserSyncProcessor;
use App\Entity\Directory\People;
use PHPUnit\Framework\TestCase;

class UpdateUserMessageHandlerTest extends TestCase
{
    public function testHandlerExecutesSyncWhenContextIsResolved(): void
    {
        $peopleIri = '/api/people/123';
        $message = new UpdateUserMessage($peopleIri);

        $people = $this->createMock(People::class);
        $context = $this->createMock(UserSyncContext::class);

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->expects($this->once())
            ->method('getResourceFromIri')
            ->with($peopleIri)
            ->willReturn($people);

        $userSyncProcessor = $this->createMock(UserSyncProcessor::class);
        $userSyncProcessor->expects($this->once())
            ->method('resolveSyncContext')
            ->with($people)
            ->willReturn($context);

        $userSyncProcessor->expects($this->once())
            ->method('executeSync')
            ->with($context);

        $handler = new UpdateUserMessageHandler($iriConverter, $userSyncProcessor);
        $handler($message);
    }

    public function testHandlerDoesNothingWhenContextIsNull(): void
    {
        $peopleIri = '/api/people/456';
        $message = new UpdateUserMessage($peopleIri);

        $people = $this->createMock(People::class);

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->expects($this->once())
            ->method('getResourceFromIri')
            ->with($peopleIri)
            ->willReturn($people);

        $userSyncProcessor = $this->createMock(UserSyncProcessor::class);
        $userSyncProcessor->expects($this->once())
            ->method('resolveSyncContext')
            ->with($people)
            ->willReturn(null);

        $userSyncProcessor->expects($this->never())
            ->method('executeSync');

        $handler = new UpdateUserMessageHandler($iriConverter, $userSyncProcessor);
        $handler($message);
    }
}
