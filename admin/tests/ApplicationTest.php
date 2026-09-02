<?php

declare(strict_types=1);

namespace Tests;

use App\Application;
use App\Error\ErrorHandler;
use App\Job\LoggerAwareCommand;
use App\Logger\CommandLoggerFactory;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplicationTest extends TestCase
{
    use ProphecyTrait;

    public function testApplicationWrapsTheConsoleApplication(): void
    {
        $_SERVER['argv'] = ['bin/console', 'dadoo:runrun', 'The Crystals'];

        $consoleApplicationProphecy = $this->prophesize(ConsoleApplication::class);
        $commandProphecy = $this->prophesize(LoggerAwareCommand::class);

        $errorHandlerProphecy = $this->prophesize(ErrorHandler::class);
        $loggerFactoryProphecy = $this->prophesize(CommandLoggerFactory::class);
        $loggerProphecy = $this->prophesize(Logger::class);

        $loggerProphecy->info('Job is about to start with arguments: The Crystals')->shouldBeCalledTimes(1);
        $loggerProphecy->error('ah no no no no no noooo')->shouldBeCalledTimes(1);
        $logger = $loggerProphecy->reveal();

        $input = new ArgvInput();
        $outputProphecy = $this->prophesize(OutputInterface::class);
        $output = $outputProphecy->reveal();

        $commandProphecy->setLogger($logger)->shouldBeCalledTimes(1);

        $command = $commandProphecy->reveal();

        $errorHandlerProphecy->setLogger($logger)->shouldBeCalledTimes(1);

        $consoleApplicationProphecy->add($command)->shouldBeCalledTimes(1);
        $consoleApplicationProphecy->find('dadoo:runrun')->shouldBeCalledTimes(1)->willReturn($command);
        $consoleApplicationProphecy->setCatchExceptions(false)->shouldBeCalledTimes(1);
        $consoleApplicationProphecy->setAutoExit(false)->shouldBeCalledTimes(1);
        $consoleApplicationProphecy->run($input, $output)->shouldBeCalledTimes(1)->willThrow(new \Exception('ah no no no no no noooo'));

        $loggerFactoryProphecy->createLogger($command)->shouldBeCalledTimes(1)->willReturn($logger);

        $application = new Application($consoleApplicationProphecy->reveal(), $errorHandlerProphecy->reveal(), $loggerFactoryProphecy->reveal());

        $application->add($command);
        $application->run($input, $output);
    }

    public function testDefaultCommandIsListIfNoneIsProvided(): void
    {
        $_SERVER['argv'] = ['bin/console'];

        $consoleApplicationProphecy = $this->prophesize(ConsoleApplication::class);
        $commandProphecy = $this->prophesize(Command::class);

        $errorHandlerProphecy = $this->prophesize(ErrorHandler::class);
        $loggerFactoryProphecy = $this->prophesize(CommandLoggerFactory::class);

        $input = new ArgvInput();
        $outputProphecy = $this->prophesize(OutputInterface::class);
        $output = $outputProphecy->reveal();

        $command = $commandProphecy->reveal();

        $consoleApplicationProphecy->add($command)->shouldBeCalledTimes(1);
        $consoleApplicationProphecy->find('list')->shouldBeCalledTimes(1)->willReturn($command);
        $consoleApplicationProphecy->setCatchExceptions(true)->shouldBeCalledTimes(1);
        $consoleApplicationProphecy->setAutoExit(false)->shouldBeCalledTimes(1);
        $consoleApplicationProphecy->run(Argument::that(static function (InputInterface $input) {
            return 'list' === $input->getFirstArgument();
        }), $output)->shouldBeCalledTimes(1)->willThrow(new \Exception('ah no no no no no noooo'));

        $application = new Application($consoleApplicationProphecy->reveal(), $errorHandlerProphecy->reveal(), $loggerFactoryProphecy->reveal());

        $application->add($command);
        $application->run($input, $output);
    }

    public function testDefaultCommandIsListIfNonExistingCommandIsProvided(): void
    {
        $_SERVER['argv'] = ['bin/console', 'tu me vois plus'];

        $consoleApplicationProphecy = $this->prophesize(ConsoleApplication::class);

        $consoleApplicationProphecy->find('tu me vois plus')->shouldBeCalledTimes(1)->willThrow(new CommandNotFoundException('not found'));

        $commandProphecy = $this->prophesize(Command::class);

        $errorHandlerProphecy = $this->prophesize(ErrorHandler::class);
        $loggerFactoryProphecy = $this->prophesize(CommandLoggerFactory::class);

        $input = new ArgvInput();
        $outputProphecy = $this->prophesize(OutputInterface::class);
        $output = $outputProphecy->reveal();

        $command = $commandProphecy->reveal();

        $consoleApplicationProphecy->add($command)->shouldBeCalledTimes(1);
        $consoleApplicationProphecy->find('list')->shouldBeCalledTimes(1)->willReturn($command);
        $consoleApplicationProphecy->setCatchExceptions(true)->shouldBeCalledTimes(1);
        $consoleApplicationProphecy->setAutoExit(false)->shouldBeCalledTimes(1);
        $consoleApplicationProphecy->run(Argument::that(static function (InputInterface $input) {
            return 'list' === $input->getFirstArgument();
        }), $output)->shouldBeCalledTimes(1)->willThrow(new \Exception('ah no no no no no noooo'));

        $application = new Application($consoleApplicationProphecy->reveal(), $errorHandlerProphecy->reveal(), $loggerFactoryProphecy->reveal());

        $application->add($command);
        $application->run($input, $output);
    }
}
