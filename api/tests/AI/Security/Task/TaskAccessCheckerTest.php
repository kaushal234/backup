<?php

declare(strict_types=1);

namespace App\Tests\AI\Security\Task;

use App\AI\Security\Task\TaskAccessChecker;
use App\Entity\Directory\People;
use App\Entity\Task\Task;
use App\Security\Provider\Confidential\Task\ConfidentialSecurityProviderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

final class TaskAccessCheckerTest extends TestCase
{
    public function testGrantsWhenTaskIsNotConfidential(): void
    {
        $task = $this->createTask(confidential: false);

        $checker = new TaskAccessChecker([], $this->createMock(Security::class));

        self::assertTrue($checker->isGranted($task));
    }

    public function testGrantsWhenCurrentUserIsAssignee(): void
    {
        $user = $this->createMock(People::class);
        $task = $this->createTask(confidential: true, assignee: $user);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($user);

        $checker = new TaskAccessChecker([], $security);

        self::assertTrue($checker->isGranted($task));
    }

    public function testGrantsWhenAConfidentialProviderGrants(): void
    {
        $task = $this->createTask(confidential: true);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->createMock(People::class));

        $denying = $this->createMock(ConfidentialSecurityProviderInterface::class);
        $denying->method('isGranted')->with($task)->willReturn(false);

        $granting = $this->createMock(ConfidentialSecurityProviderInterface::class);
        $granting->method('isGranted')->with($task)->willReturn(true);

        $checker = new TaskAccessChecker([$denying, $granting], $security);

        self::assertTrue($checker->isGranted($task));
    }

    public function testDeniesWhenConfidentialAndNoProviderGrants(): void
    {
        $task = $this->createTask(confidential: true);

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->createMock(People::class));

        $provider = $this->createMock(ConfidentialSecurityProviderInterface::class);
        $provider->method('isGranted')->with($task)->willReturn(false);

        $checker = new TaskAccessChecker([$provider], $security);

        self::assertFalse($checker->isGranted($task));
    }

    public function testSupportsOnlyTask(): void
    {
        $checker = new TaskAccessChecker([], $this->createMock(Security::class));

        self::assertTrue($checker->supports(Task::class));
        self::assertFalse($checker->supports(\stdClass::class));
    }

    private function createTask(bool $confidential, ?People $assignee = null, ?People $createdBy = null): Task
    {
        $task = $this->createMock(Task::class);
        $task->method('isConfidential')->willReturn($confidential);
        $task->assignee = $assignee;
        $task->createdBy = $createdBy;

        return $task;
    }
}
