<?php

declare(strict_types=1);

namespace Tests\Job\Calendar;

use App\Job\Calendar\ScheduledTasksCommand;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class ScheduledTasksCommandTest extends TestCase
{
    use ProphecyTrait;

    public function testExecute(): void
    {
        // Define a dummy tldST class before ScheduledTasksCommand::execute is called.
        if (!class_exists('\tldST')) {
            eval('class tldST { public static function doTasks() {} }');
        }

        // Create a temporary calendar.inc.php file to satisfy include_once
        $tempDir = sys_get_temp_dir().'/tld_test_'.uniqid();
        mkdir($tempDir);
        file_put_contents($tempDir.'/calendar.inc.php', '<?php // empty');

        $originalIncludePath = get_include_path();
        set_include_path($tempDir.\PATH_SEPARATOR.$originalIncludePath);

        try {
            $command = new ScheduledTasksCommand();
            $loggerProphecy = $this->prophesize(LoggerInterface::class);
            $command->setLogger($loggerProphecy->reveal());

            $commandTester = new CommandTester($command);
            $exitCode = $commandTester->execute([]);

            $this->assertSame(Command::SUCCESS, $exitCode);
            $this->assertSame('calendar:task:scheduled', $command->getName());
            $this->assertSame('Generate tasks from ST', $command->getDescription());
        } finally {
            set_include_path($originalIncludePath);
            unlink($tempDir.'/calendar.inc.php');
            rmdir($tempDir);
        }
    }
}
