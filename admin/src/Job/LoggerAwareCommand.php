<?php

declare(strict_types=1);

namespace App\Job;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;

abstract class LoggerAwareCommand extends Command implements LoggerAwareInterface
{
    use LoggerAwareTrait;
}
