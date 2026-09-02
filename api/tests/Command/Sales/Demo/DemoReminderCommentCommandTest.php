<?php

declare(strict_types=1);

namespace App\Tests\Command\Sales\Demo;

use App\Command\Sales\Demo\DemoReminderCommentCommand;
use App\Entity\Sales\Demo;
use App\Notifier\Sales\Demo\DemoNotifier;
use App\Repository\Sales\DemoRepository;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DemoReminderCommentCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    final public const COMMAND = 'api:sales:reminder_comment_demos';

    public function testAsmNotifiedIfDemoNotCommentedRecently()
    {
        $demoRepositoryMock = $this->getMockBuilder(DemoRepository::class)->disableOriginalConstructor()->onlyMethods(['findUncommentedDemo'])->getMock();
        $demoNotifierProphecy = $this->prophesize(DemoNotifier::class);

        $demoRepositoryMock->expects($this->once())->method('findUncommentedDemo')->willReturn([$demo = new Demo()]);
        $demoNotifierProphecy->sendReminderEmail($demo)->shouldBeCalledTimes(1);

        self::bootKernel();
        $application = new Application(self::$kernel);
        $application->addCommand(new DemoReminderCommentCommand($demoRepositoryMock, $demoNotifierProphecy->reveal()));

        $command = $application->find(self::COMMAND);

        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
        ]);
    }
}
