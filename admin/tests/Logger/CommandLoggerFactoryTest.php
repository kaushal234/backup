<?php

declare(strict_types=1);

namespace Tests\Logger;

use App\Logger\CommandLoggerFactory;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class CommandLoggerFactoryTest extends TestCase
{
    /**
     * @dataProvider provideCommands
     */
    public function testThatLoggerNameAndLogFilesAreGeneratedFromCommand(Command $command, $expectedLogFileName): void
    {
        $factory = new CommandLoggerFactory();
        $logger = $factory->createLogger($command);

        $this->assertSame($logger->getName(), $command->getName());

        $this->assertCount(2, $logger->getHandlers());
        /** @var RotatingFileHandler $handler */
        $handler = $logger->getHandlers()[0];
        $this->assertInstanceOf(RotatingFileHandler::class, $handler);
        $this->assertStringEndsWith($expectedLogFileName, $handler->getUrl());

        $outputHandler = $logger->getHandlers()[1];
        $this->assertInstanceOf(StreamHandler::class, $outputHandler);
        $this->assertSame('php://stderr', $outputHandler->getUrl());
    }

    public function provideCommands()
    {
        yield '2 parts' => [new Command('hello:world'), \sprintf('hello_world-%s.log', date('Y-m-d'))];
        yield 'lots of parts' => [new Command('hello:is:it:me:you:are:looking:for'), \sprintf('hello_is_it_me_you_are_looking_for-%s.log', date('Y-m-d'))];
    }
}
