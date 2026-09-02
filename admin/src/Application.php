<?php

declare(strict_types=1);

namespace App;

use App\Error\ErrorHandler;
use App\Logger\CommandLoggerFactory;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Application
{
    private $decorated;
    private $errorHandler;
    private $commandLoggerFactory;

    public function __construct(ConsoleApplication $decorated, ErrorHandler $errorHandler, CommandLoggerFactory $commandLoggerFactory)
    {
        $this->decorated = $decorated;
        $this->errorHandler = $errorHandler;
        $this->commandLoggerFactory = $commandLoggerFactory;
    }

    public function add(Command $command): void
    {
        $this->decorated->add($command);
    }

    public function run(InputInterface $input, OutputInterface $output): void
    {
        if (null === $input->getFirstArgument()) {
            $input = new ArrayInput(['list']);
        }
        try {
            $command = $this->decorated->find($input->getFirstArgument());
        } catch (CommandNotFoundException $exception) {
            $output->writeln('Command not found');
            $input = new ArrayInput(['list']);
            $command = $this->decorated->find($input->getFirstArgument());
        }

        $logger = null;

        if ($command instanceof LoggerAwareInterface) {
            $logger = $this->commandLoggerFactory->createLogger($command);

            $command->setLogger($logger);
            $this->errorHandler->setLogger($logger);

            $log = 'Job is about to start';

            if (\count($arguments = (array) $_SERVER['argv']) > 2) {
                array_shift($arguments);
                array_shift($arguments);
                $log .= ' with arguments: '.implode(', ', $arguments);
            }

            $logger->info($log);
        }

        $this->decorated->setCatchExceptions(null === $logger);
        $this->decorated->setAutoExit(false);

        try {
            $this->decorated->run($input, $output);
        } catch (\Exception $e) {
            if (null !== $logger) {
                $logger->error($e->getMessage());
            }
        }
    }
}
