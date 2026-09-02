<?php

declare(strict_types=1);

namespace App\Tests\Agile;

use App\Agile\Repository\UserRepository;
use App\Agile\Resources\User;
use App\Agile\UserSyncContext;
use App\Agile\UserSyncProcessor;
use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class UserSyncProcessorTest extends TestCase
{
    private UserRepository|MockObject $userRepository;
    private PeopleRepository|MockObject $peopleRepository;
    private UserSyncContext|MockObject $userSyncContext;
    private LoggerInterface|MockObject $logger;
    private UserSyncProcessor $processor;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->peopleRepository = $this->createMock(PeopleRepository::class);
        $this->userSyncContext = $this->createMock(UserSyncContext::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->processor = new UserSyncProcessor(
            $this->userRepository,
            $this->peopleRepository,
            $this->userSyncContext,
            $this->logger
        );
    }

    public function testExecuteSyncCallsRepositoryUpdate(): void
    {
        $context = $this->createMock(UserSyncContext::class);

        $context->expects(self::once())->method('getResolvedEvent')->willReturn('user_joined');
        $agileUserToUpdate = new User();
        $agileUserToUpdate->peopleId = '123';
        $agileUserToUpdate->lastName = 'Doe';
        $agileUserToUpdate->firstName = 'John';

        $context->expects(self::once())->method('getAgileUserToUpdate')->willReturn($agileUserToUpdate);
        $context->expects(self::once())->method('getChanges')->willReturn(['some' => 'changes']);

        $this->userRepository
            ->expects($this->once())
            ->method('updateAgileUser')
            ->with(
                $agileUserToUpdate,
                ['some' => 'changes'],
                'user_joined'
            );

        $this->processor->executeSync($context);
    }

    public function testExecuteSyncLogsErrorOnFailure(): void
    {
        $agileUserToUpdate = new User();
        $agileUserToUpdate->peopleId = '456';
        $agileUserToUpdate->lastName = 'Smith';
        $agileUserToUpdate->firstName = 'Jane';

        $context = $this->createMock(UserSyncContext::class);
        $context->expects(self::once())->method('getResolvedEvent')->willReturn('user_updated');
        $context->expects(self::once())->method('getChanges')->willReturn([]);
        $context->expects(self::exactly(4))->method('getAgileUserToUpdate')->willReturn($agileUserToUpdate);

        $this->userRepository
            ->expects(self::once())->method('updateAgileUser')
            ->willThrowException(new \Exception('Update failed'));

        $this->logger
            ->expects(self::once())
            ->method('error')
            ->with(
                $this->stringContains('Something went wrong'),
                $this->callback(static function (array $context) {
                    return
                        isset($context['people'])
                        && str_contains($context['people'], '#456')
                        && str_contains($context['people'], 'Smith')
                        && str_contains($context['people'], 'Jane')
                        && isset($context['error'])
                        && str_contains($context['error'], 'Update failed');
                })
            );

        $this->processor->executeSync($context);
    }

    public function testInitializationIsCalledOnlyOnce(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $peopleRepository = $this->createMock(PeopleRepository::class);
        $userSyncContext = $this->createMock(UserSyncContext::class);
        $logger = new NullLogger();

        $userRepository
            ->expects($this->once())
            ->method('getAllUsers')
            ->willReturn([]);

        $peopleRepository
            ->expects($this->once())
            ->method('searchAllPeopleIdWithGroup')
            ->with('ACL_AUTH_AGILE')
            ->willReturn([]);

        $userSyncContext
            ->method('buildContext')
            ->willReturnSelf();
        $userSyncContext
            ->method('getResolvedEvent')
            ->willReturn(null);

        $people = $this->createMock(People::class);
        $people->method('getId')->willReturn(1);
        $people->method('isDisabled')->willReturn(false);

        $processor = new UserSyncProcessor($userRepository, $peopleRepository, $userSyncContext, $logger);

        $processor->resolveSyncContext($people);
        $processor->resolveSyncContext($people);
    }
}
