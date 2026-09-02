<?php

declare(strict_types=1);

namespace App\Logger;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;

class CommandLoggerFactory
{
    public function createLogger(Command $command)
    {
        $name = $command->getName();

        $logFileName = str_replace(':', '_', $name);

        $fileHandler = new RotatingFileHandler(__DIR__.'/../../var/logs/'.$logFileName.'.log', 10);
        $fileHandler->setFormatter(new LineFormatter());

        $outputHandler = new StreamHandler('php://stderr', Logger::DEBUG);

        return new Logger($name, [$fileHandler, $outputHandler]);
    }
}
