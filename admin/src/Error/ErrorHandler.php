<?php

declare(strict_types=1);

namespace App\Error;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ErrorHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var bool */
    private $executionSuccessful = false;

    public function __invoke(): void
    {
        if (null === $this->logger) {
            return;
        }

        if (true === $this->executionSuccessful) {
            $this->logger->info('Job finished');

            return;
        }

        $error = error_get_last();
        $this->logger->error('An error occurred', $error ?: []);
    }

    public function setExecutionSuccessful(): void
    {
        $this->executionSuccessful = true;
    }
}
